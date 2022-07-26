<?php

namespace IlBronza\Datatables\DatatablesFields\Numbers;

use IlBronza\Datatables\DatatablesFields\DatatableField;

class DatatableFieldFloor extends DatatableField
{
	// public $requiresPlaceholderElement = true;

	public function transformValue($value)
	{
		if(! $value)
			return ;

		return floor($value);
	}
}