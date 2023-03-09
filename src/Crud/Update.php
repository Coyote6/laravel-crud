<?php



namespace Coyote6\LaravelCrud\Crud;



use Coyote6\LaravelCrud\Traits\CrudRouteReturn;
use Coyote6\LaravelCrud\Traits\HasPolicy;
use Coyote6\LaravelCrud\Traits\PropertySet;
use Coyote6\LaravelCrud\Traits\RouteParameters;
use Coyote6\LaravelCrud\Traits\SuccessMessage;
use Coyote6\LaravelCrud\Traits\Templates;

use Coyote6\LaravelForms\Form\Form;
use Coyote6\LaravelForms\Livewire\Component;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;



abstract class Update extends Component {
	
	use CrudRouteReturn,
		PropertySet,
		SuccessMessage,
		Templates,
		RouteParameters,
		HasPolicy,
		AuthorizesRequests;
	
	// Required Properties:
	//
	// public Example $model;
	// public $title = 'Title of Form/Page';
	//
	
	// Recommended Optional Properties
	//
	// Note:
	//		Route & Route Key must both be set to use the no JavaScript fallback.
	//
	// protected $route = 'admin.example.update';
	// public $routeKey = 'example';
	// public $successMessage = 'The model was updated successfully.';
	// protect $crudRoute = 'admin.example';
	//
	
	// Optional Properties the are required for Crud Route's
	// that have a parent parameter. The model
	// will need a method to return its parent id.
	//
	// protected $crudRouteKey = 'parent-example'
	// protected $crudRouteParentIdMethod = 'parentId'
	//
	
	// Other Optional Properties
	//
	// public $template = 'livewire.admin.example.update';
	//
	
	
	// Listeners
	protected $listeners = [
		'show' => 'show'
	];
	
	// Default template and directory.
	protected $defaultTemplate = 'livewire.forms.crud--update';
	protected $defaultTemplateDir = 'laravel-crud';
	
	protected $defaultUpdateMessage = 'The model was successfully updated.';


	
	//
	// Required Methods
	//
	
	// Set update model when initiated.
	// Use type hinting to instantiate the model.
	//
	// Note:
	//		Be sure to name the model variable
	//		parameter to match the model.
	//		Laravel may not set it properly
	//		otherwise.
	//
	// public function mount (Example $example = null) {
	//	if (is_null ($example)) {
	//		$example = Example::make();
	//	}
	//	$this->model = $example;
	// }
	

	// This is called from the Crud template
	// when the Edit button is clicked.
	// 
	// Note:
	//		Be sure to name the model variable
	//		parameter to match the model.
	//		Laravel may not set it properly
	//		otherwise.
	//
	// public function show (Example $example) {
	//	if ($this->model->isNot($example)) {
	//		$this->model = $example;
	//		$this->resetErrorBag();
	//	}
	//	$this->emitUp('toggleEditModal');
	// }
	//
	
	// This is called from the reset button
	// on the form, and resets the form
	// back to its last saved value.
	//
	// Note:
	//		Be sure to name the model variable
	//		parameter to match the model.
	//		Laravel may not set it properly
	//		otherwise.	
	//
	// public function resetForm (Example $example) {
	//	$this->model = $example;
	//	$this->resetErrorBag();
	// }
	//
	
	// This method is called from the normal
	// Laravel request when JavaScript is
	// shutoff.  It should mimic your mount
	// method with its parameters (minus the
	// null setting) so the model is properly 
	// hydrated before processing the form.
	//
	// public function updateFallback (Example $example) {
	//	$this->model = $example;
	//	return $this->processUpdateFallback();
	// }
	//
	
	//
	// Methods to override
	//
	
	// Add the fields to the form.
	//
	protected function formFields (&$form) {}

	protected function prevalidate () {}
	protected function prevalidateFallback (&$data) {}
	
	protected function alterValuesFallback (&$vals) {}

	protected function presave (&$vals) {}
	protected function presaveFallback (&$vals) {}
	
	protected function postSave (&$vals) {}
	protected function postSaveFallback(&$vals) {}
	
	
	// Override if you need to do any customizations
	// to the model before saving.
	//
	public function update () {
		$this->prevalidate();
		$vals = $this->validate();
		$this->presave ($vals);
		$this->save ($vals);
		$this->postSave ($vals);
	}
	
	
	
	// Override if you need to do any customizations
	// to the model before saving.
	//
	public function processUpdateFallback () {

		$data = request()->all();
		$this->prevalidateFallback ($data);
		
		$vals = $this->form()->validate($data);
		if (array_key_exists ('submit', $vals)) {
			unset ($vals['submit']);
		}

		$this->alterValuesFallback ($vals);
		
		foreach ($vals as $key => $val) {
			$this->model->$key = $val;
		}
		
		$this->presaveFallback ($vals);
		$this->save ($vals);
		$this->postSaveFallback ($vals);
		$this->flashSuccess ($this->successMessage());
		return back();
	}
	
	
	// Override if you need to do any customizations
	// during or after saving the model.
	//
	protected function save (&$vals) {
		if ($this->hasPolicy ($this->model)) {
    		$this->authorize('update', $this->model);
    	}
		$this->model->save();
		$this->emitUp ('refreshItems');
		$this->emitUp ('toggleEditModal');
		$this->notifySuccess ($this->successMessage());
	}
	
	
	//
	// Update Form
	//
	
    
    public function generateForm () {
	    
	    $form = new Form ($this->formOptions());
	    $form->method ('PUT')
	    	->addAttribute ('wire:submit.prevent', 'update');
	    
	    if ($this->hasRoute()) {
		    $form->action ($this->route());
	    }
	    		
		$this->formFields ($form);
		
		//
		// Try and build an actual link
		//
		if ($this->crudRouteUrl()) {
			
			$closeAttr = false;
			if (!$this->isFallbackRoute()) {
				$closeAttr = 'wire:click.prevent="$emitUp(\'toggleEditModal\')"';
			}
					
			$form->html('cancel')
				->content ('<a class="inline-block py-2 px-4 border rounded-md text-sm leading-5 font-medium focus:outline-none focus:border-blue-300 focus:shadow-outline-blue transition duration-150 ease-in-out field field--button form-input" ' . $closeAttr . 'href="' . $this->crudRouteUrl() . '">Cancel</a>')
				->groupWithButtons();
				
		}
		// If not no link is available and not on the fallback, just set a button.
		else if (!$this->isFallbackRoute()) {
			$form->button ('cancel')
				->content ('Cancel')
				->addAttribute ('wire:click', '$emitUp(\'toggleEditModal\')')
				->groupWithButtons();
		}
		
		$form->button ('reset')
			->content ('Reset')
			->addAttribute ('wire:click', 'resetForm(\'' . $this->model->getKey() . '\')')
			->groupWithButtons();
		
		$s = $form->submitButton ('submit');
		$s->content = 'Save';
				
		return $form;
		
    }
    
    public function formOptions () {
	    return [
		    'lw' => $this,
		    'cache' => false,
		    'template-dir' => 'laravel-crud',
		    'theme' => 'minimal'		    
	    ];
    }
    

}
