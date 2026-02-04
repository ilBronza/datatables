<?php

namespace IlBronza\Datatables\DatatablesFields\Numbers;

use IlBronza\Datatables\DatatablesFields\Numbers\DatatableFieldBaseNumber;

class DatatableFieldInteger extends DatatableFieldBaseNumber
{
	// public $requiresPlaceholderElement = true;

	public function transformValue($value)
	{
		if(! $value)
			return ;

		return floor($value);
	}
}