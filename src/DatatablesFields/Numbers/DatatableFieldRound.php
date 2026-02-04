<?php

namespace IlBronza\Datatables\DatatablesFields\Numbers;

use IlBronza\Datatables\DatatablesFields\Numbers\DatatableFieldBaseNumber;

class DatatableFieldRound extends DatatableFieldBaseNumber
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