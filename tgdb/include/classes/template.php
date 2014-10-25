<?PHP


//template system
//supports templates in the webroot/template folder
//supports template variables in the form of {VARNAME} in the template.
class template {
	private static $parsedtokens = array(); //stores the parsed html as tplfile => token array
	private $vars;
	private $file;
	private $html;
	
	//private $varlocations; //format: array(array(varname,varlocation))

	
	//name should be the name of a .tpl template file in webroot/template folder
	//name should be given without the .tpl extension
	//vars should be an associative array in the form of varname => vardata
	public function template($name, $vars = array()) {
		if (!file_exists('templates/'.$name.'.tpl')) {
			throw new InvalidArgumentException("No such file for template ".$name);
			return;
		}
		if (!is_assoc($vars)) {
			throw new InvalidArgumentException("2nd argument (vars) is not an associative array");
			return;
		}
		
		$this->html = file_get_contents('templates/'.$name.'.tpl'); //not cached because the disk cache will do a better job then we will.
		$this->file = $name;
		$this->vars = $vars;
		//$this->preprocess();
		$this->tokenize();
	}
	
	//this finds and saves the location of all {} template variables
	public function preprocess() {
		$t = timing::gettime();
		if ((array)$this->varlocations === $this->varlocations)
			return; //we've already done this.
		$this->varlocations = array();
		$found = false;
		$foundi = 0;
		
		for ($i = 0;$i < strlen($this->html); $i++) {
			if ($this->html[$i] == '{') {
				$found = true; 
				$foundi = $i;
				continue;
			}
			if ($found) {
				if ($this->html[$i] == '}') {
					$this->varlocations[] = array(substr($this->html, $foundi+1, $i-$foundi-1),$foundi);
					$found = false;
				}
				if (ctype_space($this->html[$i]))
					$found = false; //white space, {} vars don't have whitespace, reset and move on
			}
		}
		//sort from highest to lowest
		usort($this->varlocations, function ($a1,$a2) {return $a2[1]-$a1[1];});
		timing::tracktime('preprocess()', $t);
	}
	
	private function tokenize() {
		$t = timing::gettime();
		if (isset(self::$parsedtokens[$this->file]))
			return; //we've already done this.
		self::$parsedtokens[$this->file] = array();
		
		$brackettoken = "";
		$stringtoken = "";
		$bracket = false;
		
		for ($i = 0;$i < strlen($this->html); $i++) {
			if ($this->html[$i] == '{') {
				if ($bracket) { //the innermost open bracket is what counts, no nesting
					$stringtoken .= $brackettoken;
					$brackettoken = "";
				}
				$bracket = true;
			}
			
			if ($bracket) {
			$brackettoken .= $this->html[$i];
			if ($this->html[$i] == '}') {
					self::$parsedtokens[$this->file][] = $stringtoken;
					self::$parsedtokens[$this->file][] = substr($brackettoken,1,-1);
					
					$brackettoken = "";
					$stringtoken = "";
					$bracket = false;
					continue;
				}
				if (ctype_space($this->html[$i])) { //white space, {} vars don't have whitespace, reset and move on
					$stringtoken .= $brackettoken;
					
					$brackettoken = "";
					$bracket = false;
					
					continue;
				}
			} else {
				$stringtoken .= $this->html[$i];
			}
		}
		
		self::$parsedtokens[$this->file][] = $stringtoken; //add the remaining token
		
		timing::tracktime('tokenize()', $t, true);
	}
	
	//takes the template, and replaces tokens with data passed to the template object.
	public function process() {
		//$t = timing::gettime();
		$tokens = self::$parsedtokens[$this->file];
		$tokenifskips = 0;

		foreach ($tokens as $key=>$token) {
			
			//token arrays will always be string literal,token,string literal,token,...,string literal so we only do stuff if the index is odd.
			if ($key & 1) {
			$tokentype = 0; //0 = normal 1 = IFDEF 2 = IFNDEF 3 = ENDIF
				if ($token[0] == '#') {//its a special/conditional token (#ifdef style defines)
					$tokenarg = '';
					if (strpos($token,':') === FALSE) {//no argument
						$tokenarg = substr($token,1);
						$tokend = "";
					} else {
						$i = strpos($token,':');
						$tokenarg = substr($token,1,$i-1);
						$tokend = substr($token,$i+1);
					}
					//figure out what the fuck kind of token we are working with
					switch ($tokenarg) {
						case 'IFDEF':
							$tokentype = 1;
							if ($tokenarg == "")
								$tokentype = 0;
							break;
						case 'IFNDEF':
							$tokentype = 2;
							if ($tokenarg == "")
								$tokentype = 0;
							break;
						case 'ENDIF':
							$tokentype = 3;
							break;
						default:
							echo "(". __FILE__ .":". __LINE__ .")process token parse error! data: ".$tokenarg." ||| ".$token;
							break;
					}
					if ($tokentype != 0)
						$tokens[$key] = ""; //blank out the special token
					if ($tokend != "")
						$token = $tokend; //if there was an argument, set the 'token' to that now that we know the type
				}
				if ($tokenifskips > 0) {//are we currently in a failed conditional block? 
					if ($tokentype == 2 || $tokentype == 1)
						$tokenifskips++;//if this is the start of another conditional block, increase the number of {#ENDIF} we have to see to stop skipping
					else if ($tokentype == 3)
						$tokenifskips--; //if this is an {#ENDIF}, lower the skip counter
					
					$tokens[$key] = ""; //blank out the token so it doesn't get joined later
					continue; //skip
				}
				if ($tokentype == 3 || $token == "")
					continue; //endif token, no need to go on
					
				if (!isset($this->vars[$token])) { //look for the variable
					if ($tokentype == 1) { //no variable, and its a IFDEF, start skipping
						$tokenifskips++;
					}
					if ($tokentype == 0)
						$tokens[$key] = '{'.$token.'}'; //variable token that wasn't assigned, treat the token as a string literal instead
						
					continue;//no variable, no dice.
				}
				$tokenvar = $this->vars[$token];
				//was defined, if this is a conditional do some more checking
				if (($tokentype == 2 && $tokenvar !== FALSE) || ($tokentype == 1 && $tokenvar === FALSE)) {//cheating, but easier then making a way to undefined a variable
				
					$tokenifskips++;//failed conditional, start skipping
					continue;
				}
				if ($tokentype != 0)
					continue; //special token, no need to do replacement stuff
				
				if ($tokenvar instanceof template) { //if our variable is another template, process it before replacing the token with it
					$tokens[$key] = $tokenvar->process();
					$this->vars[$token] = "";//templates can only be process()'ed once
				} else {
					$tokens[$key] = $tokenvar; //replace the token with the contents of the variable
				}
				
			} else if ($tokenifskips > 0) { //if we are in a failed conditional, blank out the string literal token so it doesn't get joined later
				$tokens[$key] = "";
			}
		}
		$this->vars = array(); //blank out our template variable list for memory reasons

		return join($tokens); //return the token array as one concatenated string
	}
	
	
	/*
	public function process() {
		$t = timing::gettime();
		$processedtext = $this->html;
		
		foreach ($this->varlocations as $tplvar) {
			if (array_key_exists($tplvar[0], $this->vars)) {
				if ($this->vars[$tplvar[0]] instanceof template) {
					$tt = timing::gettime();
					$vdata = $this->vars[$tplvar[0]]->process();
					$t +=  timing::gettime()-$tt; //we don't want to track the time twice.
					unset($this->vars[$tplvar[0]]); //templates are only used one.
				} else {
					$vdata = $this->vars[$tplvar[0]];
				}
				$processedtext = substr_replace($processedtext, $vdata, $tplvar[1], strlen($tplvar[0]) + 2);
			}
		}
		$this->resetvars();
		timing::tracktime('process()', $t);
		return $processedtext;
	}
	*/
	
	
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