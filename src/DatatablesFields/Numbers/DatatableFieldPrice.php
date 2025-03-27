<?php

namespace IlBronza\Datatables\DatatablesFields\Numbers;

use IlBronza\Datatables\DatatablesFields\DatatableField;
use IlBronza\Datatables\DatatablesFields\FieldTypesTraits\DecimalsTrait;

use function implode;

class DatatableFieldPrice extends DatatableField
{
	use DecimalsTrait;

	public $width = '4em';
	public $decimalSeparator = ',';
	public $thousandsSeparator = '.';

	public ? string $textAlign = 'right';

	public $suffix = ' €';
	public int $decimals = 2;


	public function getExportResultOptionsEditor()
	{
		return " if(item) item = item.replace('.', ''); ";

	}

	public function transformValue($value)
	{
		if(! $value)
			return ;

		return number_format($value, $this->getDecimals(), $this->decimalSeparator, $this->thousandsSeparator);
	}
}