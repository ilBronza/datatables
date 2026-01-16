<?php

use IlBronza\Datatables\Http\Controllers\ColumnSettingsController;
use IlBronza\Datatables\Http\Controllers\ColumnShowingController;

Route::group([
	'middleware' => ['web', 'auth'],
	'prefix' => 'datatables-manager',
	'namespace' => 'IlBronza\Datatables\Http\Controllers'
], function()
{
	Route::post('{tableKey}/column-showing/update', [ColumnShowingController::class, "update"])->name('datatables.columnShowing.update');

	Route::post('{tableKey}/column-settings/update', [ColumnSettingsController::class, 'update'])->name('datatables.columnSettings.update');



});

