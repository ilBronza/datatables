<?php

namespace IlBronza\Datatables\Http\Controllers;

use App\Http\Controllers\Controller;
use IlBronza\Datatables\Models\DatatableUserData;
use Illuminate\Http\Request;

class TableUiSettingsController extends Controller
{
	public function update(Request $request, string $tableKey)
	{
		$request->validate([
			'key' => 'string|required|max:64|in:mainHeaderHidden,filtersHidden',
			'value' => 'nullable'
		]);

		$datatableUserData = DatatableUserData::provideByTableKey($tableKey);

		$datatableUserData->data->setUiSetting($request->input('key'), $request->input('value'));
		$datatableUserData->save();

		return [
			'success' => true
		];
	}
}

