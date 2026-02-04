<?php

namespace IlBronza\Datatables\DatatablesFields\Numbers;

use IlBronza\Datatables\DatatablesFields\FieldTypesTraits\DecimalsTrait;
use IlBronza\Datatables\DatatablesFields\Numbers\DatatableFieldBaseNumber;

class DatatableFieldPrice extends DatatableFieldBaseNumber
{
	use DecimalsTrait;

	public $width = '4em';
	public $decimalSeparator = ',';
	public $thousandsSeparator = '.';

	public ?string $textAlign = 'right';

	public ?string $suffix = ' €';
	public int $decimals = 2;

	public function getExportResultOptionsEditor()
	{
		return " if(item) item = item.replace('.', ''); ";
	}

	public function getCustomColumnDefSingleSortResult()
	{
		return "
			return item.replace('" . $this->thousandsSeparator . "', '').replace('" . $this->suffix . "', '');
		";
	}

	public function transformValue($value)
	{
		if (! $value)
			return;

		return number_format($value, $this->getDecimals(), $this->decimalSeparator, $this->thousandsSeparator);
	}
}