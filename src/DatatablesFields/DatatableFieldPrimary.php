<?php

namespace IlBronza\Datatables\DatatablesFields;

class DatatableFieldPrimary extends DatatableField
{
	public $requireElement = true;
	public $rowId = true;
	public $visible = false;

	public function transformValue($value)
	{
		if(! $value)
			return ;

		return $value->getKey();
	}
}