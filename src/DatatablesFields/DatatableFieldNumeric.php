<?php

namespace IlBronza\Datatables\DatatablesFields;

class DatatableFieldNumeric extends DatatableField
{
	public $digits = 2;

	public function transformValue($value)
	{
		if(! $value)
			return ;

		return number_format($value, $this->digits);
	}

}