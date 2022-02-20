<?php

namespace IlBronza\Datatables\DatatablesFields\Numbers;

use IlBronza\Datatables\DatatablesFields\DatatableField;

class DatatableFieldPrice3 extends DatatableField
{
	// public $requiresPlaceholderElement = true;

	public function transformValue($value)
	{
		if(! $value)
			return ;

		return round($value, 3);
	}
}