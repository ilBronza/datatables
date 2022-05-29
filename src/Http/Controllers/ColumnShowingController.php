<?php

namespace IlBronza\Datatables\Http\Controllers;

use App\Http\Controllers\Controller;
use Auth;
use IlBronza\Datatables\Models\DatatableUserData;
use Illuminate\Http\Request;

class ColumnShowingController extends Controller
{
	private function hideColumn()
	{
		$this->datatableUserData->data->hideColumn(request()->input('columnName'));
	}

	private function showColumn()
	{
		$this->datatableUserData->data->showColumn(request()->input('columnName'));
	}

	public function manageAction()
	{
		$this->datatableUserData = DatatableUserData::provideByTableKey(
			$this->tableKey
		);

		$action = request()->input('action');

		$this->{$action}();

		$this->datatableUserData->save();

		return [
			'success' => true
		];
	}

	public function update(Request $request, string $tableKey)
	{
		$request->validate([
			'action' => 'string|required|max:64|in:' . implode(",", DatatableUserData::getAllowedActions()),
			'columnName' => 'string|required|max:128'
		]);

		$this->tableKey = $tableKey;

		return $this->manageAction();

	}
}