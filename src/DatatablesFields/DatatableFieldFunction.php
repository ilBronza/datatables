<?php

namespace IlBronza\Datatables\DatatablesFields;

class DatatableFieldFunction extends DatatableField
{
	public function transformValue($value)
	{
		return $value->{$this->function}();
	}	
}