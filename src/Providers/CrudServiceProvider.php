<?php


namespace Coyote6\LaravelCrud\Providers;


use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ServiceProvider;
use Illuminate\View\Compilers\BladeCompiler;


class CrudServiceProvider extends ServiceProvider {
	

	/**
	 * Register services.
	 *
	 * @return void
	 */
	public function register() {
		$this->loadViewsFrom (__DIR__ . '/../Resources/views', 'laravel-crud');
	}


	/**
	 * Bootstrap services.
	 *
	 * @return void
	 */
	public function boot() {

		//
		// Blade Components
		//
		$this->configureComponents();

		//
		// CRUD Dev
		//
		
		
		Builder::macro('toCsv', function ($excludeFields = null) {
			$results = $this->get();
			
			if (is_string ($excludeFields)) {
				if ($excludeFields == '') {
					$excludeFields = [];
				}
				else {
					$excludeFields = explode(',', $excludeFields);
				}
			}
			else if (!is_array ($excludeFields) || is_null ($excludeFields)) {
				$excludeFields = [];
			}
			
			if ($results->count() < 1) return;
			
			// Remove the exclude fields from the titles.
			$attrs = (array) $results->first()->getAttributes();
			foreach ($attrs as $k => $v) {
				if (in_array ($k, $excludeFields)) {
					unset ($attrs[$k]);
				}
			}
			
			$titles = implode (',', array_keys($attrs));
			
			
			$values = $results->map(function ($result) use ($excludeFields) {
			
				$attrs = $result->getAttributes();
				foreach ($attrs as $k => $v) {
					if (in_array ($k, $excludeFields)) {
						unset ($attrs[$k]);
					}
				}
				
				
				return implode(',', collect($attrs)->map(function ($thing) {
					return '"'.$thing.'"';
				})->toArray());
				
			});
			
			$values->prepend($titles);
			
			return $values->implode("\n");
		});

	}
  
	
	/**
     * Configure the Jetstream Blade components.
     *
     * @return void
     */
    protected function configureComponents () {
	    $this->callAfterResolving (BladeCompiler::class, function () {
			$this->registerComponent('search');
		});
    }
  
  
	/**
     * Register the given component.
     *
     * @param  string  $component
     * @return void
     */
    protected function registerComponent (string $component) {
		Blade::component ('laravel-crud::components.' . $component, 'crud-' . $component);
    }
}
