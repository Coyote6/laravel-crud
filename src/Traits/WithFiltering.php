<?php
  
  
namespace Coyote6\LaravelCrud\Traits;


trait WithFiltering {

	
	public $showFilters;
	
	
	public function toggleShowFilters () {
	    $this->useCachedRows();
	    $this->showFilters = ! $this->showFilters;
	}
	
	
}
