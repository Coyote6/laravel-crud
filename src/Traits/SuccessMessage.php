<?php
  
  
namespace Coyote6\LaravelCrud\Traits;


trait SuccessMessage {
	
	public function successMessage () {
		if ($this->propertySet ('successMessage')) {
			return $this->successMessage;
		}
		return $this->defaultSuccessMessage;
	}
	
}