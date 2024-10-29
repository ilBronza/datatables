<?php

namespace IlBronza\Datatables\DatatablesFields\Utilities;

use IlBronza\Datatables\DatatablesFields\DatatableFieldFlat;

class DatatableFieldSorting extends DatatableFieldFlat
{
	public $order = [
		'priority' => 99999
	];



	public function transformValue($value)
	{
		return $value;
	}
}