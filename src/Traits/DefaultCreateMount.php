<?php
	
	
namespace Coyote6\LaravelCrud\Traits;


trait DefaultCreateMount {
	
	public function mount () {
		$this->model = $this->makeModel();
	}

	public function storeFallback () {
		$this->model = $this->makeModel();
		return $this->processStoreFallback();
	}

}