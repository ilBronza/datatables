<?php

namespace IlBronza\Datatables\DatatablesFields;

class DatatableFieldButton extends DatatableField
{
	public function transformValue($value)
	{
		return $value->{$this->button}();
	}	
}