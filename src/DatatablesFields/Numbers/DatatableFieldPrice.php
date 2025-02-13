<?php

namespace IlBronza\Datatables\DatatablesFields\Numbers;

use IlBronza\Datatables\DatatablesFields\DatatableField;
use IlBronza\Datatables\DatatablesFields\FieldTypesTraits\DecimalsTrait;

class DatatableFieldPrice extends DatatableField
{
	use DecimalsTrait;

	public $width = '4em';

	public ? string $textAlign = 'right';

	public $suffix = ' €';
	public int $decimals = 2;

	public function transformValue($value)
	{
		if(! $value)
			return ;

		return number_format($value, $this->getDecimals());
	}
}