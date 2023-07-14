<?php

namespace IlBronza\Datatables\DatatablesFields\Numbers;

use IlBronza\Datatables\DatatablesFields\DatatableField;

class DatatableFieldRound extends DatatableField
{
	public $decimals = 2;

	public function getDecimals()
	{
		return $this->decimals;
	}

	public function transformValue($value)
	{
		if(! $value)
			return ;

		return round($value, $this->getDecimals());
	}
}