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



abstract class Delete extends Component {
	
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
	// public $title = 'Title of Page';
	//
	
	// Recommended Optional Properties
	//
	// Note:
	//		Route & Route Key must both be set to use the no JavaScript fallback.
	//
	// protected $route = 'admin.example.update';
	// public $routeKey = 'example';
	// public $successMessage = 'The model was deleted successfully.';
	// public $confirmationMessage = 'Are you sure you want to delete this item?';
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
	protected $defaultTemplate = 'livewire.forms.crud--delete';
	protected $defaultTemplateDir = 'laravel-crud';
	
	protected $defaultSuccessMessage = 'The model was successfully deleted.';
	protected $defaultConfirmationMessage = 'Are you sure you want to delete this item?';
	
	protected string $submitButtonText = 'Delete';
	protected string $cancelButtonText = 'Cancel';

	
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
	//	}
	//	$this->emitUp('toggleDeleteModal');
	// }
	//
	
	// This method is called from the normal
	// Laravel request when JavaScript is
	// shutoff.  It should mimic your mount
	// method with its parameters (minus the
	// null setting) so the model is properly 
	// hydrated before processing the form.
	//
	// public function destroyFallback (Example $example) {
	//	$this->model = $example;
	//	return $this->processDestroyFallback();
	// }
	
	//
	// Methods to override
	//
	
	// Add the fields to the form.
	//
	protected function formFields (&$form) {
		$form->html ('confirmation')
			->content ('<div>' . $this->confirmationMessage() . '</div>');
	}
	
	
	protected function prevalidate () {}
	protected function prevalidateFallback (&$data) {}
	
	protected function alterValuesFallback (&$vals) {}

	protected function predelete (&$vals) {}
	protected function predeleteFallback (&$vals) {}
	
	protected function postDelete (&$vals) {}
	protected function postDeleteFallback(&$vals) {}
	
	protected function getSubmitButtonText () {
		return $this->submitButtonText;
	}
	
	protected function getCancelButtonText () {
		return $this->cancelButtonText;
	}
	
	
	// Override if you need to do any customizations
	// to the model before deleting it.
	//
	public function destroy () {
		
		$this->prevalidate();
		$vals = $this->validate();
		
		$this->predelete ($vals);
		$this->delete ($vals);
		return $this->postDelete ($vals);
	
	}
	
	
	// Override if you need to do any customizations
	// to the model before deleting it.
	//
	public function processDestroyFallback () {
		
		$data = request()->all();
		$this->prevalidateFallback ($data);
		
		$vals = $this->form()->validate($data);
		$this->alterValuesFallback ($vals);
		
		$this->predeleteFallback ($vals);
		$this->delete ($vals);
		$this->postDeleteFallback ($vals);
		
		if ($this->isFallbackRoute() && $this->crudRouteUrl()) {
			$this->flashSuccess ($this->successMessage());
			return redirect($this->crudRouteUrl());
		}
	}
	
	
	// Override if you need to do any customizations
	// during or after saving the model.
	//
	protected function delete (&$vals) {
		
		if ($this->hasPolicy ($this->model)) {
    		$this->authorize('delete', $this->model);
    	}
		
		$this->model->delete();
		$this->emitUp ('refreshItems');
		$this->emitUp ('toggleDeleteModal');
		$this->notifySuccess ($this->successMessage());

	}
	
	
	// Override if you wish to add variables to the message.
	//
	protected function confirmationMessage () {
		if ($this->propertySet ('confirmationMessage')) {
			return $this->confirmationMessage;
		}
		return $this->defaultConfimationMessage;
	}

	
	//
	// Create Form
	//
	
	
    
    public function generateForm () {
	    
	    $form = new Form ($this->formOptions());
	    $form->method ('DELETE')
	    	->addAttribute ('wire:submit.prevent', 'destroy');
	    
	    if ($this->hasRoute()) {
		    $form->action ($this->route());
	    }
	    
	    $id = $this->model->getKey();
	    $form->hidden('item_id')
	    	->lw('model.id')
	    	->required()
	    	->value($id)
	    	->addRules(['string', 'in:' . $id]);
	    		
		$this->formFields ($form);
		
		//
		// Try and build an actual link
		//
		if ($this->crudRouteUrl()) {
			
			$closeAttr = false;
			if (!$this->isFallbackRoute()) {
				$closeAttr = 'wire:click.prevent="$emitUp(\'toggleDeleteModal\')"';
			}
					
			$form->html('cancel')
				->content ('<a class="inline-block py-2 px-4 border rounded-md text-sm leading-5 font-medium focus:outline-none focus:border-blue-300 focus:shadow-outline-blue transition duration-150 ease-in-out field field--button form-input" ' . $closeAttr . 'href="' . $this->crudRouteUrl() . '">' . $this->getCancelButtonText() . '</a>')
				->groupWithButtons();
				
		}
		// If not no link is available and not on the fallback, just set a button.
		else if (!$this->isFallbackRoute()) {
			$form->button ('cancel')
				->content ($this->getCancelButtonText())
				->addAttribute ('wire:click', '$emitUp(\'toggleDeleteModal\')')
				->groupWithButtons();
		}
		
		
		$form->submitButton ('submit')
			->content ($this->getSubmitButtonText());
				
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
