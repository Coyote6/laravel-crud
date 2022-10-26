<?php
  
  
namespace Coyote6\LaravelCrud\Traits;


use Livewire\WithPagination;


trait WithPerPagePagination {

	
	use WithPagination;
	
	
	public $perPage = 25;

	
	public function initializeWithPerPagePagination () {
		if (session()->has ('perPage')) {
			$this->perPage = session()->get ('perPage', $this->perPage);
		}
		else if ($this->propertySetIsInteger ('resultsPerPage')) {
			$this->perPage = $this->resultsPerPage;
		}
    }


    public function updatedPerPage ($value) {
        session()->put('perPage', $value);
    }


    public function applyPagination ($query) {
        return $query->paginate ($this->perPage);
    }
  
  
}