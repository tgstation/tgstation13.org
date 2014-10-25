<?PHP


//template system
//supports templates in the webroot/template folder
//supports template variables in the form of {VARNAME} in the template.
class template {
	private $vars;
	private $file;
	private $html;
	
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
		$this->html = file_get_contents('templates/'.$name.'.tpl');
		$this->file = $name;
		$this->vars = $vars;
	}

	public function process() {
		$search = array();
		$replace = array();
		foreach ($this->vars as $vname => $vdata) {
			$search[] = '{'.$vname.'}';
			$replace[] = $vdata;
		}
		return str_replace($search, $replace, $this->html);
	}
	
	public function setvar($name, $var) {
		$this->vars[$name] = $var;
	}
	
	public function resetvars($vars = array()) {
		if (!is_assoc($vars)) {
			throw new InvalidArgumentException("argument is not an associative array");
			return;
		}
		$this->vars = $vars;
		
	}
}

?>