<?php
  
  
namespace Coyote6\LaravelCrud\Traits;


trait CrudRouteReturn {
	
	

	public function hasCrudRoute () {
		static $hasRoute;
		if (is_null ($hasRoute)) {
			$hasRoute = false;
			if ($this->propertySet ('crudRoute')) {
				$hasRoute = true;
			}
		}
		return $hasRoute;
	}
	
	
	public function hasCrudRouteKey () {
		static $hasKey;
		if (is_null ($hasKey)) {
			$hasKey = false;
			if ($this->propertySet ('crudRouteKey')) {
				$hasKey = true;
			}
		}
		return $hasKey;
	}
	
	public function hasCrudRouteParentIdMethod () {
		static $hasKey;
		if (is_null ($hasKey)) {
			$hasKey = false;
			if ($this->propertySet ('crudRouteParentIdMethod')) {
				$hasKey = true;
			}
		}
		return $hasKey;
	}
	
	
	public function crudRouteUrl () {
		if (
			$this->hasCrudRoute() && $this->hasCrudRouteKey() && 
			$this->hasCrudRouteParentIdMethod() && is_callable ([$this->model, $this->crudRouteParentIdMethod])
		) {
			$methodName = $this->crudRouteParentIdMethod;
			$parentId = $this->model->$methodName();
			return route ($this->crudRoute, $this->routeParameters() + [$this->crudRouteKey => $parentId]);
		}
		else if ($this->hasCrudRoute()) {
			return route ($this->crudRoute, $this->routeParameters());
		}
		return false;
	}
	
	
		
}