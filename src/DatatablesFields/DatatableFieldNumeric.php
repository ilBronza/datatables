<?php

namespace IlBronza\Datatables\DatatablesFields;

class DatatableFieldNumeric extends DatatableField
{
	public $round = 2;

	public function transformValue($value)
	{
		if(! $value)
			return ;

		return round($value, $this->round);
	}
}