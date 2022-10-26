<?php
  
  
namespace Coyote6\LaravelCrud\Traits;


trait WithSorting {

	
	public $sorts = [];

	
	public function sortBy ($field) {
						
		if (!isset ($this->sorts[$field])) {
			$this->sorts[$field] = 'asc';
			return;
		}
		
		if ($this->sorts[$field] === 'asc') {
			$this->sorts[$field] = 'desc';
			return;
		}
		
		unset ($this->sorts[$field]);
		
	}
	
	
	public function applySorting ($query) {
		
		foreach ($this->sorts as $field => $direction) {
			$query->orderBy ($field, $direction);
		}
		return $query;
	}
  
  
}
