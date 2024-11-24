<?php

namespace IlBronza\Datatables\DatatablesFields;

class DatatableFieldSelectRowCheckbox extends DatatableField
{
	public $width = '20px';
	public $filterable = false;

	public function transformValue($value)
	{
		return;
	}
}