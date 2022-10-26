<?php


namespace Coyote6\LaravelCrud\Crud;


use Validator;

use Coyote6\LaravelCrud\Crud\Csv;
use Coyote6\LaravelForms\Form\Form;
use Livewire\Component;

use Livewire\WithFileUploads;


abstract class Import extends Component {
		
	use WithFileUploads;
	
	// Optional Properties
	//
	// protected $refreshEvent = 'refreshExamples';
	//
	
	public $showModal = false;
	public $upload;
	public $columns;
	public $fieldColumnMap = [
		'id' => '',
        'title' => '',
    ];
    
    
	protected $listeners = [
		'toggleImportModal' => 'toggleModal'
	];
    
    //
    // Required Methods
    //
    
    //
    //
    // @return array
    //
    abstract protected function purposedColumnMapping ();
    
    // Return an array of validation rules
    // for each row to be checked against.
    //
    // @return Valator rules array
    //
    abstract protected function rowValidationRules ();
    
    // The create function is called for each row to be added.
    //
    // protected function create ($fields) {
	//	Example::create ($fields);
	// }
	//
    abstract protected function create ($fields);

        
    
    protected $rules = [
        'fieldColumnMap.name' => 'required',
    ];
    
    protected $customAttributes = [
        'fieldColumnMap.id' => 'id',
        'fieldColumnMap.name' => 'name',
    ];
    
    
    //
	// Import Functionality
	//
	
	
	protected function emitEvent () {
		if (property_exists ($this, 'refreshEvent') && is_string ($this->refreshEvent) && $this->refreshEvent != '') {
			return $this->emit ($this->refreshEvent);
		}
		return $this->emit ('refreshItems');
    }
	
    public function toggleModal () {
	    $this->showModal = !$this->showModal;
    }
    

	public function updatingUpload ($value) {
        Validator::make(
            ['upload' => $value],
            ['upload' => 'required|mimes:txt,csv'],
        )->validate();
    }
    
    
    public function updatedUpload () {
        $this->columns = Csv::from ($this->upload)->columns();
        $this->attemptPurposedColumnMapping();
    }
    
    
	public function attemptPurposedColumnMapping () {

		foreach ($this->columns as $column) {
			
			$match = collect ($this->purposedColumnMapping())->search (function ($options) use ($column) {
				return in_array (strtolower ($column), $options);
			});
			
			if ($match) {
				$this->fieldColumnMap[$match] = $column;
			}
			
		}
	}
	
	
	public function import () {
		
		$this->validate();
		
		$rows = 0;
		$importCount = 0;
		$errors = [];
		
		Csv::from ($this->upload)
		    ->eachRow(function ($row) use (&$rows, &$importCount, &$errors) {
			    
			    $rows++;
			    $fields = $this->extractFields($row);
			    
				$validator = Validator::make ($fields, $this->rowValidationRules());
								
				if ($validator->fails()) {
					$errors[$rows] = $validator->errors();
				}
				else {
			        $this->create ($fields);
			        $importCount++;
			    }
		    });
		
		$this->reset();
		$this->emitEvent();

		
		if ($importCount > 0) {
			$this->notifySuccess ('Imported '.$importCount.' transactions!');
		}
		
		$errorCount = count ($errors);
		if ($errorCount > 0) {
			$this->notifyError ($errorCount . ' records failed to import. A log has been downloaded with the errors.');			
			return response()->streamDownload (function() use ($errors) {
				print "Row,Errors\n";
				foreach ($errors as $row => $errorBag) {
					print $row . ',' .  implode (' | ', $errorBag->all()) . "\n";
				}
			}, 'errors.csv');
		}
	}
	
	
	protected function extractFields ($row) {
		
        $attributes = collect ($this->fieldColumnMap)
            ->filter()
            ->mapWithKeys (function ($heading, $field) use ($row) {
                return [$field => $row[$heading]];
            })
            ->toArray();

        return $attributes;
        
    }
    
    
    public function render () {
	    return view('laravel-crud::livewire.forms.crud--import');
    }
    
	
}
