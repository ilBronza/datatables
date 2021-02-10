<?php

namespace IlBronza\Datatables\DatatablesFields;

class DatatableFieldFunction extends DatatableField
{
	public function transformValue($value)
	{
		if(! $value)
			return;

		return $value->{$this->function}();
	}	
}