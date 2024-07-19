<?php
	

namespace Coyote6\LaravelCrud\Crud;


use Livewire\Component;
use Illuminate\View\ComponentAttributeBag;

use Coyote6\LaravelCrud\Traits\PropertySet;
use Coyote6\LaravelCrud\Traits\RouteParameters;
use Coyote6\LaravelCrud\Traits\Templates;
use Coyote6\LaravelCrud\Traits\WithBulkActions;
use Coyote6\LaravelCrud\Traits\WithCachedRows;
use Coyote6\LaravelCrud\Traits\WithPerPagePagination;
use Coyote6\LaravelCrud\Traits\WithSorting;

use Coyote6\LaravelForms\Form\Form;


abstract class Crud extends Component {
	
	
	use PropertySet,
		Templates,
		WithPerPagePagination, 
		WithBulkActions, 
		WithSorting, 
		WithCachedRows,
		RouteParameters;
	
	
	//
	// Optional Properties
	//
	
	// A comma separated string or array of 
	// fields to exclude from the export.
	//
	// public $bulkExportExcludedFields = ['field_name_to_exclude'];
	//
	
	// A blade component containing an icon
	// to display in front of the first field
	// and on the no results screen.
	// 
	// 	public $icon = 'icons.cash';

	
	public $showCreateModal = false;
	public $showEditModal = false;
	public $showDeleteModal = false;
	public $showDeleteSelectedModal = false;
	public $showFilters = false;
	
	
	///
	// Properties that can be set
	//
	// public $displayUpdateFormLink = true;
	// public $displayDeleteFormLink = true;
	// public $updaeFormLinkName = 'Edit';
	// public $deleteFormLinkName = 'Delete';
	
	public $customOperationsBefore = true;
	

	protected $listeners = [
		'refreshItems' => '$refresh',
		'toggleCreateModal' => 'toggleCreateModal',
		'toggleEditModal' => 'toggleEditModal',
		'toggleDeleteModal' => 'toggleDeleteModal',
		'toggleDeleteSelectedModal' => 'toggleDeleteSelectedModal',
	];
	
	
	protected $queryString = [
		'sorts'
	];

	
	
	// Default template and directory.
	protected $defaultTemplate = 'livewire.forms.crud';
	protected $defaultTemplateDir = 'laravel-crud';
	
	// 
	public $operations = [];
	public $customModals = [];
	

    /**
	 * Return a Builder instance of the model.
	 *
	 *	@see Illuminate\Database\Eloquent\Builder
	 *
	 *	@example
	 *		Model::query()
	 *			->when($this->filters['search'], fn($query, $search) => $query->search('column_name', $search));
	 *
	 *	@return Builder;
	 */
	abstract protected function getQueryProperty ();

	
	//
	// Once booted, add any filters in the query string.
	//
	public function booted () {
		collect (request()->input())->filter (function ($value, $filter) {
			if ($filter == 'filters') {	
        		if (is_array ($value)) {
			    	foreach ($value as $k => $v) {
				    	if (isset ($this->filters[$k])) {
				    		$this->filters[$k] = $v;
				    	}
			    	}
		    	}
        	}
    	});
	}

	
	public function rules () {

		$rules = [];
		$forms = [
			$this->searchForm(),
			$this->deleteSelectedForm()
		];
		foreach ($forms as $form) {
			foreach ($form->lwRules() as $name => $rule) {
				$rules[$name] = $rule;
			}
		}
		return $rules;
	}
	
	protected function columns () {
		if (property_exists($this, 'columns')) {
			return $this->columns;
		}
		return [];
	}
	
	
	public function toggleCreateModal () {
		$this->showCreateModal = !$this->showCreateModal;
	}
	
	public function toggleEditModal () {
		$this->showEditModal = !$this->showEditModal;
	}
	
	public function toggleDeleteModal () {
		$this->showDeleteModal = !$this->showDeleteModal;
	}
	
	public function toggleDeleteSelectedModal () {
		$this->showDeleteSelectedModal = !$this->showDeleteSelectedModal;
	}
	
	public function toggleImportModal () {
		$this->emit ('toggleImportModal');
	}
	
	public function updated ($field) {
		$this->validateOnly ($field);
	}
	
	
	public function getItemsProperty() {
		return $this->cache (function () {
            return $this->applyPagination ($this->query);
        });
	}
	
	
	protected function customOperations() {
		$ops = [];
		if (property_exists ($this, 'operations')) {
			foreach ($this->operations as $label => $op) {
				$ops[$label] = $op;
			}
		}
		return $ops;
	}
	
	protected function updateFormLinkName() {
		if (property_exists ($this, 'updateFormLinkName')) {
			return $this->updateFormLinkName;
		}
		return 'Edit';
	}
	
	protected function deleteFormLinkName() {
		if (property_exists ($this, 'deleteFormLinkName')) {
			return $this->deleteFormLinkName;
		}
		return 'Delete';
	}
	
	protected function displayUpdateFormLink() {
		if (property_exists ($this, 'displayUpdateFormLink')) {
			return $this->displayUpdateFormLink;
		}
		return true;
	}
	
	protected function displayDeleteFormLink() {
		if (property_exists ($this, 'displayDeleteFormLink')) {
			return $this->displayDeleteFormLink;
		}
		return true;
	}
	
	
	protected function operations () {

		$ops = [];
		
		if ($this->customOperationsBefore) {
			foreach ($this->customOperations() as $label => $op) {
				$ops[$label] = $op;
			}
		}
		
		if (property_exists ($this, 'updateForm') && $this->displayUpdateFormLink() && !isset ($ops[$this->updateFormLinkName()])) {
			$ops[$this->updateFormLinkName()] = [
				'author_permission' => '',
				'permissions' => $this->updatePermissions(),
				'route' => property_exists ($this, 'updateRoute') ? $this->updateRoute : '',
				'component' => $this->updateForm,
				'method' => 'show',
			];
		}
		
		
		if (property_exists ($this, 'deleteForm') && $this->displayDeleteFormLink() && !isset ($ops[$this->deleteFormLinkName()])) {
			$ops[$this->deleteFormLinkName()] = [
				'author_permission' => '',
				'permissions' => $this->deletePermissions(),
				'route' => property_exists ($this, 'deleteRoute') ? $this->deleteRoute : '',
				'component' => $this->deleteForm,
				'method' => 'show',
			];
		}
		
		
		if (!$this->customOperationsBefore) {
			foreach ($this->customOperations() as $label => $op) {
				$ops[$label] = $op;
			}
		}
		
		return $ops;
	}
	
	public function createPermissions () {
		return (property_exists ($this, 'createPermissions')) ? $this->createPermissions : '';
	}
	
	public function updatePermissions () {
		return (property_exists ($this, 'updatePermissions')) ? $this->updatePermissions : '';
	}
	
	public function deletePermissions () {
		return (property_exists ($this, 'deletePermissions')) ? $this->deletePermissions : '';
	}
	
	public function operationLinks ($item) {
		$links = [];
		$user = auth()->user();

		foreach ($this->operations() as $label => $op) {
			
			if (!is_array ($op)) {
				continue;
			}
			
			$authorPermissionIsset = isset ($op['author_permission']) && $op['author_permission'] != '';
			$orPermissionIsset = isset ($op['permissions']) && $op['permissions'] != '';

			if ($authorPermissionIsset && $orPermissionIsset && !is_null($item->author_id)) {
				if (!is_null ($user) && $user->id == $item->author_id && $user->hasPermission ($op['author_permission'])) {
					$links[$label] = $this->generateLink ($label, $op, $item);
				}
				else if (!is_null ($user) && $user->hasPermission ($op['permissions'])) {
					$links[$label] = $this->generateLink ($label, $op, $item);
				}
			}
			else if ($authorPermissionIsset && !is_null($item->author_id)) {
				if (!is_null ($user) && $user->id == $item->author_id && $user->hasPermission ($op['author_permission'])) {
					$links[$label] = $this->generateLink ($label, $op, $item);
				}
				else if (!is_null ($user) && $user->hasPermission ($op['permissions'])) {
					$links[$label] = $this->generateLink ($label, $op, $item);
				}
			}
			else if ($orPermissionIsset) {
				if (!is_null ($user) && $user->hasPermission ($op['permissions'])) {
					$links[$label] = $this->generateLink ($label, $op, $item);
				}
			}
			else {
				$links[$label] = $this->generateLink ($label, $op, $item);
			}
			
		}
		
		return $links;
		
	}
	
	
	public function routeVariableName () {
		if (property_exists ($this, 'routeVariable') && is_string ($this->routeVariable) && $this->routeVariable != '') {
			return $this->routeVariable;
		}
		if (property_exists ($this, 'routeVariableName') && is_string ($this->routeVariableName) && $this->routeVariableName != '') {
			return $this->routeVariable;
		}
		return 0;
	}
	
	
	protected function generateLink ($key, $op, $item) {
		
		$link = [];
		if ($op['route']) {
			
			if (isset ($op['include_route_params']) && $op['include_route_params'] == false) {
				$params = [$this->routeVariableName() => $item->getKey()];
			}
			else {
				$params = $this->routeParameters() + [$this->routeVariableName() => $item->getKey()];
			}
			
			
			$link['href'] = route ($op['route'], $params);
		
			
			if ($this->useEditModal() && isset ($op['component'])) {
				
				$modalExists = false;
				
				if (
					($key == $this->updateFormLinkName() && property_exists ($this, 'updateForm')) || 
					($key == $this->deleteFormLinkName() && property_exists ($this, 'deleteForm'))
				) {
					$modalExists = true;
				}
				else if (property_exists ($this, 'updateForm') && $op['component'] == $this->updateForm) {
					$modalExists = true;
				}
				else if (property_exists ($this, 'deleteForm') && $op['component'] == $this->deleteForm) {
					$modalExists = true;
				}
				else {
					foreach ($this->customModals as $m) {
						if ($m['compoenent'] == $op['component']) {
							$modalExists = true;
							break;
						}
					}
				}
				
				if ($modalExists) {
					$link['@click'] = 'overrideClick';
					$link['data-method'] = 'show';
					$link['data-id'] = $item->getKey();
					$link['data-component'] = $op['component'];
				}
			}
			
		}
		else {
			$link['wire:click'] = '$' . "emitTo('" . $op['component'] . "', 'show', '" . $item->getKey . "')";
		}
		
		
		return new ComponentAttributeBag ($link);
		
	}

	
	protected function defaultTemplateVars () {
		
		if (property_exists ($this, 'title')) {
			$this->title = strip_tags($this->title, '<a><b><i><strong><em>');
		}
		
		return [
			'title' => 'Dashboard',
			'searchForm' => $this->searchForm(),
			'createForm' => false,
			'updateForm' => false,
			'deleteForm' => false,
			'deleteSelectedForm' => $this->deleteSelectedForm(),
			'importForm' => $this->importForm(),
			'createRoute' => false,
			'updateRoute' => false,
			'deleteRoute' => false,
			'useCreateModal' => $this->useCreateModal(),
			'useEditModal' => $this->useEditModal(),
			'useDeleteModal' => $this->useDeleteModal(),
			'items' => $this->items,
			'icon' => false,
			'hasBulkActions' => $this->hasBulkActions(),
			'columns' => $this->columns()
		];
	}
	
	
	public function render () {
		
		$vars = array_merge ($this->defaultTemplateVars(), $this->templateVars());
		$user = auth()->user();
		
		if ($this->createPermissions() != '' && (!$user || !$user->hasPermission ($this->createPermissions()))) {
			$vars['createForm'] = false;
			$vars['createRoute'] = false;
			$vars['useCreateModal'] = false;
		}
		if ($this->updatePermissions() != '' && (!$user || !$user->hasPermission ($this->updatePermissions()))) {
			$vars['updateForm'] = false;
			$vars['updateRoute'] = false;
			$vars['useEditModal'] = false;
		}
		if ($this->deletePermissions() != '' && (!$user || !$user->hasPermission ($this->deletePermissions()))) {
			$vars['deleteForm'] = false;
			$vars['deleteRoute'] = false;
			$vars['useDeleteModal'] = false;
		}
		return view ($this->template(), $vars);
	}
		
	
	/**
	 * Method to add template variables to the blade template.
	 */
	protected function templateVars () {
		return [];
	}
		
	
	
	public function search () {
		
		if (!property_exists ($this, 'filters')) {
			return;
		}
		
		$this->validateOnly ('filters.*');

		return redirect()->route($this->routeName, $this->routeParameters() + [
			'filters' => $this->filters,
			'sorts' => $this->sorts
		]);
		
	}
	
	
	//
	// Override to do custom search form buttons
	//
	// @example
	//		[
	//			'My Button' => [
	//				'use-modal' => true,
	//				'component' => 'my.livewire.component',
	//				'href' => route('my.route'),
	//				// Add any link attributes you want, it will be turned into an Attribute bag and rendered.
	//			]
	//		]
	//	
	// @return array
	//
	public function searchFormButtons () {		
		if (property_exists ($this, 'searchFormButtons')) {
			return $this->searchFormButtons;
		}
		return [];
	}
	
	
	public function compileSearchFormButtons () {
		
		$buttons = [];
		
		foreach ($this->searchFormButtons() as $label => $button) {
			if (isset ($button['use-modal']) && $button['use-modal'] === true && isset ($button['component']) && is_string ($button['component'])) {
				$slug = 'show' . str_replace(' ', '', ucwords(preg_replace('/[^a-zA-Z0-9]/', ' ', $label))) . 'Modal';
				$this->customModals[$slug] = [
					'component' => $button['component'],
					'title' => $label
				];
				$button['wire:click.prevent'] = '$' . "toggle('" . $slug . "')";
			}
			if (isset ($button['component'])) {
				unset ($button['component']);
			}
			if (isset ($button['use-modal'])) {
				unset ($button['use-modal']);
			}
			$buttons[$label] = new ComponentAttributeBag ($button);
		}
	
		return $buttons;
		
	}
	
	public function importForm () {
		if (!property_exists ($this, 'importForm')) {
			return false;
		}
		return $this->importForm;
	}
	
	
	public function hasCreateForm () {
	    if ($this->propertySet ('createForm') && $this->createRoute()) {
		    return true;
	    }
	    return false;
    }
    
    
    public function hasBulkActions () {
	    if ($this->propertySetFalse ('bulkActions')) {
		    return false;
	    }
	    return true;
    }
    
    
    public function createRoute () {
	    
	    $user = auth()->user();
	    if ($this->createPermissions() != '' && (!$user || !$user->hasPermission ($this->createPermissions()))) {
			return false;
		}
		
		if ($this->propertySet ('createRoute')) {
			return route ($this->createRoute, $this->routeParameters());
		}
		return false;
    }
    
    
    public function useCreateModal () {
	    if ($this->propertySetTrue ('useCreateModal')) {
		    return true;
	    }
	    else if ($this->propertySetFalse ('useCreateModal')) {
		    return false;
	    }
		else if ($this->propertySetFalse ('useModals')) {
			return false;
		}
		return true;
	}
	
	
	public function useEditModal () {
		if ($this->propertySetTrue ('useEditModal')) {
		    return true;
	    }
	    else if ($this->propertySetFalse ('useEditModal')) {
		    return false;
	    }
		else if ($this->propertySetFalse ('useModals')) {
			return false;
		}
		return true;
	}
	
	
	public function useDeleteModal () {
		if ($this->propertySetTrue ('useDeleteModal')) {
		    return true;
	    }
	    else if ($this->propertySetFalse ('useDeleteModal')) {
		    return false;
	    }
		else if ($this->propertySetFalse ('useModals')) {
			return false;
		}
		return true;
	}
	
	
	public function updateForm () {
		if (!property_exists ($this, 'updateForm')) {
			return false;
		}
		return $this->updateForm;
	}
	
	
	public function cacheForms () {
		if (!property_exists ($this, 'cacheForms') || !is_bool ($this->cacheForms)) {
			return false;
		}
		return $this->cacheForms;
	}
	
	
	/**
	 * Methods to add fields to the preformed Forms
	 */
    public function advancedSearchFormFields (&$form) {}
	
	
	public function deleteSelectedFormFields (&$form) {
		$h = $form->html ('confirmation');
		$h->content = '<div>' . $this->deleteSelectedConfirmationMessage . '</div>';
	}
	
	
	public function getDeleteSelectedConfirmationMessageProperty () {
		if (count ($this->selected) > 1) {
			return 'Are you sure you want to delete the selected items?';
		}
		else {
			return 'Are you sure you want to delete the selected item?';
		}
	}
	
	
	/**
     * Form methods return a Form instance.
     *
     * @see Coyote6\LaravelForms\Form\Form
     *
     * @return Form
     */
    public function searchForm () {
	    
		static $form;
		if (is_null ($form)) {
			$form = $this->generateSearchForm();
		}
		return $form;
		
    }
    
    
    public function deleteSelectedForm () {
	    
	    static $form;
		if (is_null ($form)) {
			$form = $this->generateDeleteSelectedForm();
		}
		return $form;
	
	}
	
	
	protected function searchFormOptions () {
	    return [
	    	'lw' => $this, 
	    	'cache' => $this->cacheForms(), 
	    	'template-dir' => 'laravel-crud', 
	    	'theme' => 'minimal'
	    ];
    }
	
	/**
     * Form methods return a Form instance.
     *
     * @see Coyote6\LaravelForms\Form\Form
     *
     * @return Form
     */
    public function generateSearchForm () {
	    
		$form = new Form ($this->searchFormOptions());
		$form->addAttribute ('wire:submit.prevent', 'search');
		
		 if ($this->hasRoute()) {
		    $form->action ($this->route());
	    }
		
		$form->text('search')
			->lwDebounce('filters.search')
			->placeholder('Search'); 
		
		if (count ($this->items) > 10) {
			$pp = $form->select ('per-page')
				->lw('perPage')
				->label ('Per Page')
				->addOptions ([
					'10' => 10,
					'25' => 25,
					'100' => 100,
				])
				->noDefaultOption()
				->value ($this->perPage)
				->addAttribute ('style', 'padding-bottom: 6px; padding-top: 6px;')
				->removeClass ('mt-1');
					
			$pp->formItemTag()
				->addClass ('flex items-center')
				->removeClass ('mt-6');
					
			$pp->labelContainerTag()
				->addClass ('mr-2');
			
			$pp->fieldContainerTag()
				->removeClass ('mt-1');
		}
		else {
			$form->html ('per-page');
		}
				
		$form->addCustomVariable ('hasImportForm', $this->importForm());
		$form->addCustomVariable ('hasCreateForm', $this->hasCreateForm());
		$form->addCustomVariable ('createRoute', $this->createRoute());
		$form->addCustomVariable ('useCreateModal', $this->useCreateModal());
		$form->addCustomVariable ('hasBulkActions', $this->hasBulkActions());
		$form->addCustomVariable ('buttons', $this->compileSearchFormButtons());
					
		$defaultFieldCount = $form->countFields();
		$this->advancedSearchFormFields ($form);

		if ($defaultFieldCount != $form->countFields()) {
			$form->html ('advanced-filters-button')
				->content ('<x-forms-button.link @click="showFilters = !showFilters">
				<span x-show="showFilters">Hide</span> Advanced Search
			</x-forms-button.link>');
			
			$form->submitButton ('submit')
				->value ('Search')
				->addAttribute ('x-show', 'false');
		}
		else {
			$form->ungroupSubmitButtons();
			$form->submitButton('advanced-filters-button')
				->value ('Search')
				->addAttribute ('x-show', 'false');
		}


		return $form;
		
    }
    
    
    protected function deleteSelectedFormOptions () {
	    return [
	    	'lw' => $this, 
	    	'cache' => $this->cacheForms(), 
	    	'template-dir' => 'laravel-crud', 
	    	'theme' => 'minimal'
	    ];
    }
	
	
	protected function generateDeleteSelectedForm () {
		
		$form = new Form ($this->deleteSelectedFormOptions());
		$form->addAttribute ('wire:submit.prevent', 'deleteSelected');
		
		if ($this->hasRoute()) {
		    $form->action ($this->route());
	    }
	    
		$this->deleteSelectedFormFields ($form);
		
		$c = $form->button ('cancel');
		$c->content = 'Cancel';
		$c->addAttribute ('wire:click', '$set(\'showDeleteSelectedModal\', false)');
		$c->groupWithButtons();
		
		$s = $form->submitButton ('submit');
		$s->content = 'Delete';
				
		return $form;
		
    }
    
    
    /**
	 * Validate the individual forms.
	 *
	 */
	public function validateSearchForm () {
		$this->validate ($this->searchForm()->lwRules());
	}


	public function getColumn ($item, $key, $column) {
		
		$link = null;
		$params = null;
		$text = null;
		$attrs = null;
		
		if (is_array ($column)) {
			if (isset ($column['route'])) {
				
				if (isset ($column['params'])) {
					if (is_array ($column['params'])) {
						foreach ($column['params'] as $paramKey => $string) {
							$objs = explode ('.', $string);
							$param = $item;
							foreach ($objs as $objKey) {
								if (is_null ($param->$objKey)) {
									$param = null;
									break;
								}
								$param = $param->$objKey;
							}
							$params[$paramKey] = $param;
						}
						try {
							$link = route ($column['route'], $params);
						} catch (\Exception $e) {
							$link = null;
						}
					}
					else if (is_string ($column['params'])) {
						try {
							$link = route ($column['route'], $column['params']);
						} catch (\Exception $e) {
							$link = null;
						}
					}
				}
				else {
					$link = route ($column['route']);
				}
				
				if (isset ($column['attrs'])) {
					$attrs = new ComponentAttributeBag ($column['attrs']);
				}
				
			}
			else if (isset ($column['action'])) {
				
				
				$action = $column['action'] . '()';
				
				
				if (isset ($column['params'])) {
					
					foreach ($column['params'] as $paramKey => $string) {
						$objs = explode ('.', $string);
						$param = $item;
						foreach ($objs as $objKey) {
							if (is_null ($param->$objKey)) {
								$param = null;
								break;
							}
							$param = $param->$objKey;
						}
						$params[$paramKey] = $param;
					}
					
					$action = $column['action'] . "('" . implode ("','", $params) . "')";
				}
					
				$actionAttrs = [
					'wire:click' => $action
				];
								
				if (isset ($column['attrs'])) {
					$actionAttrs = array_merge ($actionAttrs, $column['attrs']);
				}
				
				$attrs = new ComponentAttributeBag ($actionAttrs);
			}
			
					
		}
		
			
		$textObjKeys = explode ('.', $key);
		$text = $item;
		foreach ($textObjKeys as $tok) {
			if (is_null ($text->$tok)) {
				$text = null;
				break;
			}
			$text = $text->$tok;
		}
		
		if (isset ($column['prefix']) && is_string ($column['prefix'])) {
			$text = $column['prefix'] . $text;
		}	
		
		if (isset ($column['suffix']) && is_string ($column['suffix'])) {
			$text .= $column['suffix'];
		}

		return ['link' => $link, 'text' => $text, 'attrs' => $attrs];

	}
	
	
	public function getColumnLabel ($column) {
		
		
		if (is_string ($column)) {
			return $column;
		}
		
		if (is_array ($column) && isset ($column['label'])) {
			return $column['label'];
		}
		
		return '';
	
	}
	
	public function getColumnSort ($column, $key) {
		
		if (is_array ($column) && isset ($column['sort'])) {
			return $column['sort'];
		}
		
		if (is_string ($key)) {
			return $key;
		}
		
		return '';
	
	}

	
}
