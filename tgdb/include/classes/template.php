<?PHP
namespace tgdb;
require_once("include/classes/templateTokens.php");
use InvalidArgumentException;
//template system
//supports templates in the webroot/template folder
//supports template variables in the form of {VARNAME} in the template.
class template {
	private static $tokenSets = array();
	private $vars;
	private $file;
	

	
	//name should be the name of a .tpl template file in webroot/template folder
	//name should be given without the .tpl extension
	//vars should be an associative array in the form of varname => vardata
	public function __construct($name, $vars = array()) {
		if (!file_exists('templates/'.$name.'.tpl')) {
			throw new InvalidArgumentException("No such file for template ".$name);
			return;
		}
		if (!is_assoc($vars)) {
			throw new InvalidArgumentException("2nd argument (vars) is not an associative array");
			return;
		}
		$this->file = $name;
		$this->vars = $vars;
		if (defined("TGDB_DEV"))
			$this->setvar("DEBUG", "TRUE");
		if (isset(self::$tokenSets[$name]))
			return;
		
		self::$tokenSets[$name] = self::makeTokenSet(file_get_contents('templates/'.$name.'.tpl'));
		//$this->tokenize();
		
		
	}
	
	//Generates (and returns) a tokenSet object from the template text passed to it.
	//this function is recursive
	private static function makeTokenSet($tplText) {
		$tokenGroup = array();
		$stringLit = '';//stores the current text block to be added to a TStringLiteral object once we hit a bracket token.
		$bracketTemp = '';//store the incomplete token while we parse it
		$bracket = false;
		$conditionalToken = ''; //stores the conditional token in full for later parsing
		$conditionalSkips = 0;
		
		$k = strlen($tplText);
		for ($i = 0;$i < $k;$i++) {
			if ($tplText[$i] == '{') {
				if ($bracket) { //the innermost open bracket is what counts, no nesting
					$stringLit .= $bracketTemp;
					$bracketTemp = '';
				}
				$bracket = true;
			}
		
			if ($bracket) {
				$bracketTemp .= $tplText[$i];
				if ($tplText[$i] == '}') {
					$tType = self::tokentype($bracketTemp);
					
					//marks that we are currently looking for the closing token of a conditional block
					if ($conditionalSkips > 0) {
						if ($tType > 5) {
							$conditionalSkips++; //nested conditional block, increase the number of closing tokens we are looking for.
						} else if ($tType == 5) {
							$conditionalSkips--; //closing token, lower that same number.
						}
						if ($conditionalSkips > 0) { //Still higher then 0, dump the bracket into the string bucket and move on.
							$stringLit .= $bracketTemp;
							$bracket = false;
							$bracketTemp = '';
							continue;
						}
						//if we got here, the count was higher then 0, but now its not, we have our closing token. Time to parse the conditional token as a whole.
						
						$cType = self::tokentype($conditionalToken);//stored starting conditional token
						$cvar = '';//what var does the condition rely on.
						if (strpos($conditionalToken,':') !== false) //conditional tokens without a reliant var is valid syntax
							$cvar = (string)substr($conditionalToken, strpos($conditionalToken,':')+1, -1);
						
						//make the token and add it, parsing its block as a separate tokenset
						if ($cType == 6)
							$tokenGroup[] = (new TIfdef($cvar, self::makeTokenSet($stringLit)));
						else if ($cType == 7)
							$tokenGroup[] = (new TIfndef($cvar, self::makeTokenSet($stringLit)));
						else if ($cType == 8)
							$tokenGroup[] = (new Tarray($cvar, self::makeTokenSet($stringLit)));
						else if ($cType == 9)
							$tokenGroup[] = (new TIfempty($cvar, self::makeTokenSet($stringLit)));
						else if ($cType == 10)
							$tokenGroup[] = (new TIfnempty($cvar, self::makeTokenSet($stringLit)));
						
						//reset state and continue
						$bracket = false;
						$bracketTemp = '';
						$stringLit = '';
						$conditionalText = '';
						$conditionalSkips = 0;
						continue;
					}
					//do token stuff:
					
					//normal variable token.
					if ($tType == 0) {
						$tVar = (string)substr($bracketTemp, 1, -1);
						if ($tVar && $tVar[0] == '!') { //escaped token, treat as string lit, reset state, then continue
							$stringLit .= '{'.(string)substr($tVar, 1).'}';
							$bracketTemp = '';
							$bracket = false;
							continue;
						}
						if (!empty($stringLit))
							$tokenGroup[] = (new TStringLiteral($stringLit));
						$tokenGroup[] = (new TVariable($tVar));
						$bracketTemp = '';
						$stringLit = '';
						$bracket = false;
						continue;
					}
					
					//unhandled types, treat as string lit, reset state, and continue;
					if ($tType <= 5 || $tType > 10) {
						$stringLit .= $bracketTemp;
						$bracketTemp = '';
						$bracket = false;
						continue;
					}
						
					//if we got to here, its a conditional token, configure state to capture the rest of the token.
					
					$conditionalToken = $bracketTemp;
					$conditionalSkips = 1;
										
					if (!empty($stringLit))
						$tokenGroup[] = (new TStringLiteral($stringLit));
					$bracketTemp = '';
					$stringLit = '';
					$bracket = false;
					continue;
				}
				if (ctype_space($tplText[$i])) { //white space, {} vars don't have whitespace, reset and move on
					$stringLit .= $bracketTemp;
					$bracketTemp = "";
					$bracket = false;
					continue;
				}
			} else {
				$stringLit .= $tplText[$i];
			}
			
		}
		//reached the end, finalize things.
		$stringLit .= $bracketTemp;
				
		if ($conditionalSkips > 0) { //no matching endif block, wrap the rest of the file up into the conditional token
			$cType = self::tokentype($conditionalText);//stored starting conditional token
			$cvar = '';//what var does the condition rely on.
			if (strpos($conditionalText,':') !== false) //conditional tokens without a reliant var is valid syntax
				$cvar = (string)substr($conditionalText, strpos($conditionalText,':')+1, -1);
			
			//make the token and add it, parsing its block as a separate tokenset
			if ($cType == 6)
				$tokenGroup[] = (new TIfdef($cvar, self::makeTokenSet($stringLit)));
			else if ($cType == 7)
				$tokenGroup[] = (new TIfndef($cvar, self::makeTokenSet($stringLit)));
			else if ($cType == 8)
				$tokenGroup[] = (new TArray($cvar, self::makeTokenSet($stringLit)));
			else if ($cType == 9)
				$tokenGroup[] = (new TIfempty($cvar, self::makeTokenSet($stringLit)));
			else if ($cType == 10)
				$tokenGroup[] = (new TIfnempty($cvar, self::makeTokenSet($stringLit)));
			$stringLit = '';
		}
		
		if (!empty($stringLit))
			$tokenGroup[] = (new TStringLiteral($stringLit));
		
		return new tokenSet($tokenGroup);
	}
	private static function tokenType($token) {
		$tType = 0;
		if ($token[1] == '#') {
			$bText = (string)substr($token,2,-1);
			if (strpos($bText, ':') !== false)
				$bText = (string)substr($bText, 0, strpos($bText, ':'));

			switch ($bText) {
				case 'ENDIF':
					$tType = 5;
					break;
				case 'IFDEF':
					$tType = 6;
					break;
				case 'IFNDEF':
					$tType = 7;
					break;
				case 'ARRAY':
					$tType = 8;
					break;
				case 'IFEMPTY':
					$tType = 9;
					break;
				case 'IFNEMPTY':
					$tType = 10;
					break;
			}
		}
		return $tType;
	}
 
	
	public function process() {
		$variables = $this->vars;
		$this->resetvars();
		foreach ($variables as $name=>$data) 
			if ($data instanceof template) 
				$variables[$name] = $data->process();
			
		
		$tokenSet = self::$tokenSets[$this->file];
		
		return join($tokenSet->process($variables));
		
	}
	
	//reference version of setvarr. used with big data chucks
	//	when used with small data chunks the overhead outweighs the performance gain
	public function setvarr($name, &$var) {
		$this->vars[$name] = $var;
	}
	public function setvar($name, $var) {
		$this->vars[$name] = $var;
	}
	
	public function resetvars(array $vars = array()) {
		/*if (!is_assoc($vars)) {
			throw new InvalidArgumentException("argument is not an associative array");
			return;
		}*/
		$this->vars = $vars;
		
	}
}

?>