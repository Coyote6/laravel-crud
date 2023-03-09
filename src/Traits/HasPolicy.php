<?php
	

namespace Coyote6\LaravelCrud\Traits;


use Illuminate\Contracts\Auth\Access\Gate;


trait HasPolicy {

	public function getPolicyName ($class) {
        return app(Gate::class)->getPolicyFor($class);
    }
    
    public function hasPolicy ($class) {
        $policy = $this->getPolicyName($class);
        if (!is_null ($policy)) {
	        return true;
        }
        return false;
    }
    
}