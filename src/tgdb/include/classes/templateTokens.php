<?php
namespace tgdb;

function myErrorHandler($errno, $errstr, $errfile, $errline) {
  if ( E_RECOVERABLE_ERROR===$errno ) {
    echo "'catched' catchable fatal error\n";
	return true;
  }
  return false;
}
set_error_handler('tgdb\myErrorHandler');

//base class
abstract class templateToken {

//returns an array of template variables this token depends on.
abstract public function getRequestedVars();

//this is how we pass the token its template variable.
abstract public function setVar($var, $value = null);

//returns a string with the computed text.
abstract public function process();
}

class TStringLiteral extends templateToken {
	private $stringLiteral;
	
	public function __construct($stringLiteral = '') {
		$this->stringLiteral = $stringLiteral;
	}
	public function getRequestedVars() {
		return array();
	}
	public function setVar($variable, $value = null) {
		return;
	}
	public function process() {
		return $this->stringLiteral;
	}
	
}


class TVariable extends templateToken {
	private $variable;
	private $set = false;
	private $value = '';
	public function __construct($variable) {
		$variable = trim($variable);
		if (!$variable || empty($variable))
			throw new InvalidArgumentException('Argument must not be empty or consist of only whitespace');
		$this->variable = $variable;
	}
	
	public function getRequestedVars() {
		return array($this->variable);
	}
	
	public function setVar($variable, $value = null) {
		if ($value === null || $value === false || $variable != $this->variable)
			return;
		$this->value = $value;
		$this->set = true;
	}
	public function process() {
		//we reset state so the token can be used again.
		$value = $this->value;
		$this->value = '';
		$set = $this->set;
		$this->set = false;
		
		if (!$set)
			return '{'. $this->variable .'}';
		return $value;
	}
	
}
//conditional 
abstract class Tconditional extends templateToken {
	protected $variable;
	protected $value = '';
	protected $tokenSet;
	protected $varSet = array();
	protected $valueSet = array();
	
	public function __construct($variable, $tokenSet) {
		$this->variable = $variable;
		$this->tokenSet = $tokenSet;
		$this->varSet = $this->tokenSet->listRequestedVars();
	}
	public function getRequestedVars() {
		//doing it this way ensures our conditional variable is first but never duplicated
		return array_unique(array_merge(array($this->variable),$this->varSet));
	}
	public function setVar($variable, $value = null) {
		if ($variable == $this->variable) {
			$this->value = $value;
		}
		$this->valueSet[$variable] = $value;
	}
	public function process() {
		//copy over state
		$check = $this->checkCondition();
		$valueSet = $this->valueSet;
		
		//reset state
		$this->value = '';
		$this->valueSet = array();
		
		//do stuff
		if (!$check)
			return '';
		
		return join($this->tokenSet->process($valueSet));
	}
	
	abstract protected function checkCondition();
}
class TIfdef extends Tconditional {
	protected function checkCondition() {
		if (!$this->variable)
			return false;
		if (!$this->value)
			return false;
		return true;
	}
	
}
class TIfndef extends Tconditional {
	protected function checkCondition() {
		if (!$this->variable)
			return true;
		if (!$this->value)
			return true;
		return false;	
	}
}
class TIfempty extends Tconditional {
	protected function checkCondition() {
		if (!$this->variable)
			return true;
		if (!$this->value)
			return true;
		if (!is_array($this->value) || count($this->value) <= 0)
			return true;
		return false;	
	}
}
class TIfnempty extends Tconditional {
	protected function checkCondition() {
		if (!$this->variable)
			return false;
		if (!$this->value)
			return false;
		if (!is_array($this->value) || count($this->value) <= 0)
			return false;
		return true;
	}
}
class TArray extends TIfnempty {
	public function setVar($variable, $value = null) {
		if (!is_array($value))
			return;
		if ($variable == $this->variable) {
			$this->value = $value;
		}
	}
	public function getRequestedVars() {
		return array($this->variable);
	}
	public function process() {
		//copy over state
		$check = $this->checkCondition();
		$arr = $this->value;
		//reset state
		$this->value = '';
		$this->valueSet = array();
		
		//do stuff
		if (!$check)
			return '';
		$res = '';

		foreach ($arr as $valueSet) {
			foreach ($valueSet as $name=>$data)
				if ($data instanceof template) 
					$valueSet[$name] = $data->process();
			$res .= join($this->tokenSet->process($valueSet));
		}
		return $res;
	}
}

class tokenSet {
	private $tokens = array();
	//private $vars = array();
	private $tokenVarMappings = array(array());
	
	public function __construct($tset) {
		if ((array)$tset !== $tset)
			throw new InvalidArgumentException('Token set must be an array');
		foreach ($tset as $token) {
			if (!($token instanceof templateToken)) {
				throw new InvalidArgumentException('Token set must contain only objects of type templateToken (or its children)');
				return;
			}
		}		
		$this->tokens = $tset;
		$this->mapTokenVars();
	}
	
	private function mapTokenVars() {
		foreach ($this->tokens as $token) {
			$variables = $token->getRequestedVars();
			foreach ($variables as $variable) {
				$this->tokenVarMappings[$variable][] = $token;
			}
		}
	}
	public function listRequestedVars() {
		return array_keys($this->tokenVarMappings);
	}
	public function process($variables = array()) {

		//first we initialize the tokens with their variables.
		foreach ($this->tokenVarMappings as $variable=>$tokens) {
			if (!isset($variables[$variable])) {
				foreach ($tokens as $token)
					$token->setVar($variable);
				
				continue;
			}
			
			foreach ($tokens as $token) //pass the variable value to each token that requested it.
				$token->setVar($variable, $variables[$variable]);
		}
		
		$tokens = $this->tokens;
		foreach ($tokens as $i=>$token)
			$tokens[$i] = $token->process();
		return $tokens;
	}
}









?>