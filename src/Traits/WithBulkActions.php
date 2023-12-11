<?php
  
  
namespace Coyote6\LaravelCrud\Traits;


trait WithBulkActions {


	public $selected = [];
	public $selectPage = false;
	public $selectAll = false;
	
	
	
	public function hydrate () {
		if ($this->selectAll) {
			$this->selected = $this->items->pluck('id');
		}
	}
	
	
	public function updatedSelectPage ($value) {

		if ($value) {
			$this->selected = $this->items->pluck('id');
		}
		else {
			$this->selected = [];
		}
	}
	
	
	public function updatedSelected ($value) {
	/*	
		$pageItems = $this->items->pluck('id')->toArray();
		$allItemsCount = Permission::count();
		if (count ($this->selected) == $allItemsCount) {
			$this->selectAll = true;
			$this->selectPage = true;
		}
		else if (count ($pageItems) == count (array_intersect ($this->selected, $pageItems))) {
			$this->selectAll = false;
			$this->selectPage = true;
		}
		else {*/
			
		//}
		
		$this->selectAll = false;
		$this->selectPage = false;

	} 
	
	
	protected function bulkDeleteSuccessMessage () {
		if ($this->propertySet ('bulkDeleteSuccessMessage')) {
			return $this->bulkDeleteSuccessMessage;
		}
		return 'All of the selected items were successfully deleted';
	}
	
	
	protected function bulkExportSuccessMessage () {
		if ($this->propertySet ('bulkExportSuccessMessage')) {
			return $this->bulkExportSuccessMessage;
		}
		return 'All of the selected items were successfully exported.';
	}
	
	
	// 
	// @optionalParam $removeDataBeforeDelete
	// 
	public function getRemoveDataBeforeDelete (): bool {
		
		if (
			property_exists ($this, 'removeDataBeforeDelete') && 
			is_bool ($this->removeDataBeforeDelete)
		) {
			return $this->removeDataBeforeDelete;
		}
		
		$config = config ('crud.remove-data-before-delete', true);
		if (is_bool ($config)) {
			return $config;
		}
		
		return true;
			
	}
	
	public function deleteSelected () {
	
		if ($this->getRemoveDataBeforeDelete()) {
			
			$items = (clone $this->query)
			->unless($this->selectAll, fn ($query) => $query->whereKey ($this->selected))
			->get();
			
			foreach ($items as $item) {
				if (method_exists ($item, 'removeData')) {
					$item->removeData();
				}
				$item->delete();
			}
			
		}
		else {
			(clone $this->query)
				->unless($this->selectAll, fn ($query) => $query->whereKey ($this->selected))
				->delete();
		}
		
		$this->showDeleteSelectedModal = false;
		$this->selected = [];
		
		$this->notifySuccess ($this->bulkDeleteSuccessMessage());
	}
	
	
	protected function bulkExportExcludedFields () {
		if (property_exists($this, 'bulkExportExcludedFields')) {	// Comma separated string or array of fields to exclude from the export
			return $this->bulkExportExcludedFields;
		}
		return [];
	}
	
	
	public function exportSelected () {
		
		$this->notifySuccess ($this->bulkExportSuccessMessage());

		return response()->streamDownload (function() {
			print (clone $this->query)
				->unless($this->selectAll, fn ($query) => $query->whereKey ($this->selected))
				->toCsv($this->bulkExportExcludedFields());
		}, 'export.csv');
		
	}
	

}
