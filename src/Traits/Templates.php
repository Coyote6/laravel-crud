<?php
  
  
namespace Coyote6\LaravelCrud\Traits;


trait Templates {
	
	//
	// Templates
	//

	public function hasRoute () {
		static $hasRoute;
		if (is_null ($hasRoute)) {
			$hasRoute = false;
			if ($this->propertySet ('route')) {
				$hasRoute = true;
			}
		}
		return $hasRoute;
	}
	
	
	public function hasRouteKey () {
		static $hasKey;
		if (is_null ($hasKey)) {
			$hasKey = false;
			if ($this->propertySet ('routeKey')) {
				$hasKey = true;
			}
		}
		return $hasKey;
	}
	
	
	public function isFallbackRoute () {
		static $fallback;

		if (is_null ($fallback)) {

			$fallback = false;
			$route = $this->route();

			if (
				url()->current() == $route ||
				(
					url()->current() == route ('livewire.message', $this::getName()) &&
					url()->previous() == $route
				)
			) {
				$fallback = true;
			}	

		}

		return $fallback;
	}
	
	
	public function route ($reset = false) {
		static $route;
		if (is_null ($route) || $reset == true) {
			$route = false;
			if ($this->hasRoute() && $this->hasRouteKey() && $this->model->getKey()) {
				$route = route($this->route, $this->routeParameters() + [$this->routeKey => $this->model->getKey()]);
			}
			else if ($this->hasRoute() && $this->hasRouteKey()) {} // Do not a return a url when the key is null.
			else if ($this->hasRoute()) {
				$route = route ($this->route, $this->routeParameters());
			}
		}
		return $route;
	}
	
	
	protected function getTemplateDir () {
		if ($this->propertySet ('templateDir')) {
			return $this->templateDir;
		}
		return $this->defaultTemplateDir;
	}
	
	
	public function getTemplate () {
		if ($this->propertySet ('template')) {
			
			if ($this->isFallbackRoute()) {
				return $this->template . '-fallback';
			}
						
			return $this->template;
		}
		else if ($this->isFallbackRoute()) {
			return $this->defaultTemplate . '-fallback';
		}
		return $this->defaultTemplate;
	}
	
	
	public function template () {
				
		$template = $this->getTemplate();
		if (view()->exists ($template)) {
			return $template;
		}
		else if (view()->exists($this->getTemplateDir() . '::' . $template)) {
			return $this->getTemplateDir() . '::' . $template;
		}

		return $this->defaultTemplateDir . '::' . $this->defaultTemplate;
		
	}
	
}