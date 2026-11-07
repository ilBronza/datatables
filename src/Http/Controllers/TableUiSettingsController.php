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
			'key' => 'string|required|max:64|in:mainHeaderHidden,filtersHidden,headerStylesExpanded,columnOrder',
			'value' => 'nullable'
		]);

		if ($request->input('key') === 'headerStylesExpanded')
			$request->validate(['value' => 'required|boolean']);

		if ($request->input('key') === 'columnOrder')
			$request->validate([
				'value' => 'present|array|max:1000',
				'value.*' => 'required|string|max:128|distinct:strict',
			]);

		$datatableUserData = DatatableUserData::provideByTableKey($tableKey);

		$datatableUserData->data->setUiSetting($request->input('key'), $request->input('value'));
		$datatableUserData->save();

		return [
			'success' => true
		];
	}
}

