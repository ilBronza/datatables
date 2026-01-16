<?php

namespace IlBronza\Datatables\Http\Controllers;

use App\Http\Controllers\Controller;
use Auth;
use IlBronza\Datatables\Models\DatatableUserData;
use Illuminate\Http\Request;

use function dd;
use function json_decode;

class ColumnSettingsController extends Controller
{
	// private function hideColumn()
	// {
	// 	$this->datatableUserData->data->hideColumn(request()->input('columnName'));
	// }

	// private function showColumn()
	// {
	// 	$this->datatableUserData->data->showColumn(request()->input('columnName'));
	// }

	// public function manageAction()
	// {
	// 	$this->datatableUserData = DatatableUserData::provideByTableKey(
	// 		$this->tableKey
	// 	);

	// 	$action = request()->input('action');

	// 	$this->{$action}();

	// 	$this->datatableUserData->save();

	// 	return [
	// 		'success' => true
	// 	];
	// }

	public function update(Request $request, string $tableKey)
	{
		$request->validate([
			'columnIndex' => 'integer|required',
			'settings_json' => 'string|required',
			'columnName' => 'string|required|max:128'
		]);

		$this->tableKey = $tableKey;


		$this->datatableUserData = DatatableUserData::provideByTableKey(
			$this->tableKey
		);

		$this->datatableUserData->addColumnSettings(
			$request->columnName,
			$request->columnIndex,
			json_decode($request->settings_json, true)
		);

		return [
			'success' => true
		];
	}
}