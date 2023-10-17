<?php
  
  
namespace Coyote6\LaravelCrud\Traits;


trait WithCachedRows {
    
    // There are two ways to turn on cache:
	//		1. Set the useCache property to true
	//		2. Call the $this->useCachedRows() method
	//
	
	// @optionalProperty $useCache
	//
	// Turns the cache on and off
	//
	// @value bool
	// @example
	//	protected bool $useCache = false;
	//
	
	public function useCachedRows () {
		$this->useCache = true;
	}
	
	public function useCache () {
		if (property_exists ($this, 'useCache') && $this->useCache == true) {
			return true;
		}
		return false;
	}
	
	
	public function cache ($callback) {
		
		$cacheKey = $this->id;
	
		if ($this->useCache() && cache()->has ($cacheKey)) {
			return cache()->get ($cacheKey);
		}
	
		$result = $callback();
	
		cache()->put ($cacheKey, $result);
	
		return $result;
	}
	

}