<?php

Route::group([
	'middleware' => ['web', 'auth'],
	'prefix' => 'datatables-manager',
	'namespace' => 'IlBronza\Datatables\Http\Controllers'
], function()
{
	Route::post('{tableKey}/column-showing/update', 'ColumnShowingController@update')->name('datatables.columnShowing.update');

});

