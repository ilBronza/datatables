<?php

use IlBronza\Datatables\Http\Controllers\ColumnSettingsController;
use IlBronza\Datatables\Http\Controllers\ColumnShowingController;
use IlBronza\Datatables\Http\Controllers\TableUiSettingsController;

Route::group([
	'middleware' => [
		'web',
		'auth',
		//'datatables.roles'
	],
	'prefix' => 'datatables-manager',
	'namespace' => 'IlBronza\Datatables\Http\Controllers'
], function()
{
	Route::post('{tableKey}/column-showing/update', [ColumnShowingController::class, "update"])->name('datatables.columnShowing.update');

	Route::post('{tableKey}/column-settings/update', [ColumnSettingsController::class, 'update'])->name('datatables.columnSettings.update');

	Route::post('{tableKey}/ui-settings/update', [TableUiSettingsController::class, 'update'])->name('datatables.uiSettings.update');



});

