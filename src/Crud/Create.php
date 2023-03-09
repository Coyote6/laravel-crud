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


abstract class Create extends Component {
	
	use AuthorizesRequests,
		CrudRouteReturn,
		PropertySet,
		SuccessMessage,
		Templates,
		RouteParameters,
		HasPolicy;
	
	// Required Properties:
	//
	// public Example $model;
	// public $title = 'Title of Page';
	//
	
	// Recommended Optional Properties
	//
	// protected $route = 'admin.example.create';
	// public $successMessage = 'The model was creaeted successfully.';
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
	protected $defaultTemplate = 'livewire.forms.crud--create';
	protected $defaultTemplateDir = 'laravel-crud';
	
	protected $defaultSuccessMessage = 'The model was successfully created.';

	
	//
	// Required Methods
	//
	
	// Return the Model::class property to
	// initiate the model class.
	//
	// public function modelClass () {
	//	return Example::class;
	// }
	//
	abstract public function modelClass();
	
	// Mount the model when the page is called.
	//
	// This needs to be in the calling class
	// in case a parameter is ever set.
	//
	// public function mount () {
	//	$this->model = $this->makeModel();
	// } 
	//
	
	// This method is called from the normal
	// Laravel request when JavaScript is
	// shutoff.  It should mimic your mount
	// method with its parameters (minus the
	// null setting) or set the model itself
	// so the model is properly hydrated 
	// before processing the form.
	//
	// This needs to be in the calling class
	// in case a parameter is ever set.
	//
	// public function storeFallback () {
	//	$this->model = $this->makeModel();
	//	return $this->processStoreFallback();
	// }
	//
	
	//
	// Methods to override
	//
	
	// Add the fields to the form.
	//
	protected function formFields (&$form) {}
	
	protected function prevalidate () {}
	protected function prevalidateFallback(&$data) {}
	
	protected function alterValuesFallback(&$vals) {}

	protected function presave(&$vals) {}
	protected function presaveFallback(&$vals) {}
	
	protected function postSave(&$vals) {}
	protected function postSaveFallback(&$vals) {}

	
	// Override if you need extra functionality
	// on the show call.
	//
	// This is called from the Crud template
	// when the Edit button is clicked.
	// 
	public function show () {
		$this->emitUp ('toggleCreateModal');
	}
	
	// Override if you need to do more than set custom fields.
	//
	public function makeModel () {
		$class = $this->modelClass();
		$model = $class::make();
		$this->customModelProperties ($model);
		return $model;
	}
	
	// Override to set custom model properties to
	// use with this form, such as foreign ids.
	//
	public function customModelProperties (&$model) {}
	
	
	// Override if you need to do any customizations
	// to the model before saving.
	//
	public function store () {
		$this->prevalidate();
		$vals = $this->validate();
		$this->presave ($vals);
		$this->save ($vals);
		$this->postSave ($vals);
		$this->model = $this->makeModel();
	}
	
	
	// Override if you need to do any customizations
	// to the model before saving.
	//
	public function processStoreFallback () {
				
		$data = request()->all();
		$this->prevalidateFallback ($data);
		
		$vals = $this->form()->validate ($data);
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
		if ($this->hasPolicy ($this->modelClass())) {
    		$this->authorize('create', $this->modelClass());
    	}
		$this->model->save();
		$this->emitUp ('refreshItems');
		$this->emitUp ('toggleCreateModal');
		$this->notifySuccess ($this->successMessage());
	}

	
	
	//
	// Create Form
	//

    
    public function generateForm () {
	    
	    $form = new Form ($this->formOptions());
	    $form->method ('POST')
	    	->addAttribute ('wire:submit.prevent', 'store');
	    
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
				$closeAttr = 'wire:click.prevent="$emitUp(\'toggleCreateModal\')"';
			}
					
			$form->html('cancel')
				->content ('<a class="inline-block py-2 px-4 border rounded-md text-sm leading-5 font-medium focus:outline-none focus:border-blue-300 focus:shadow-outline-blue transition duration-150 ease-in-out field field--button form-input" ' . $closeAttr . 'href="' . $this->crudRouteUrl() . '">Cancel</a>')
				->groupWithButtons();
				
		}
		// If not no link is available and not on the fallback, just set a button.
		else if (!$this->isFallbackRoute()) {
			$form->button ('cancel')
				->content ('Cancel')
				->addAttribute ('wire:click', '$emitUp(\'toggleCreateModal\')')
				->groupWithButtons();
		}
		
		
		$form->submitButton ('submit')
			->content('Save');
				
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
