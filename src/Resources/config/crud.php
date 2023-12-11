<?php

return [	
	
	/*
	|--------------------------------------------------------------------------
	| Remove Data Before Delete
	|--------------------------------------------------------------------------
	|
	| Laravel Crud by default will attempt to call the removeData() method on
	| a model prior to deleting that model in an attempt to remove any attached
	| relationships and files before deleting a model. This default can be
	| overriden globally by setting the CRUD_REMOVE_DATA_BEFORE_DELETE env 
	| variable to false or changing it in the config file.
	|
	| @return bool
	|
	*/
	'remove-data-before-delete' => env('CRUD_REMOVE_DATA_BEFORE_DELETE', true),

];

