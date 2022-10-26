<?php
  
  
namespace Coyote6\LaravelCrud\Traits;


trait PropertySet {
	
	
	public function propertySet ($propertyName) {
		if (property_exists ($this, $propertyName) && is_string ($this->$propertyName) && $this->$propertyName != '') {
			return true;
		}
		return false;
	}

	
	public function propertySetFalse ($propertyName) {
		if (property_exists ($this, $propertyName) && $this->$propertyName == false) {
			return true;
		}
		return false;
	}
	
	
	public function propertySetTrue ($propertyName) {
		if (property_exists ($this, $propertyName) && $this->$propertyName == true) {
			return true;
		}
		return false;
	}
	
	
	public function propertySetIsInteger ($propertyName) {
		if (property_exists ($this, $propertyName) && is_integer ($this->$propertyName)) {
			return true;
		}
		return false;
	}
	
	
}