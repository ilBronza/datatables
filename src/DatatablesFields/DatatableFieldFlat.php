<?php

namespace IlBronza\Datatables\DatatablesFields;

class DatatableFieldFlat extends DatatableField
{
	// public $requiresPlaceholderElement = true;

	public function transformValue($value)
	{
		if(! $value)
			return ;

		return $value;
	}
}