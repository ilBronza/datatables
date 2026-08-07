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

	public function getCustomColumnDefSingleResultExport()
	{
		return "";
	}

	public function getCustomColumnDefSingleSortResult()
	{
		$decimalSeparator = json_encode($this->decimalSeparator);
		$thousandsSeparator = json_encode($this->thousandsSeparator);

		return "
			if(item === null || item === '')
				return item;

			const decimalSeparator = {$decimalSeparator};
			const thousandsSeparator = {$thousandsSeparator};
			let value = String(item).replace(/\\u00a0/g, '').trim();

			if(thousandsSeparator)
				value = value.split(thousandsSeparator).join('');

			if(decimalSeparator && decimalSeparator !== '.')
				value = value.split(decimalSeparator).join('.');

			const numericValue = Number(value);

			return Number.isFinite(numericValue) ? numericValue : null;
		";
	}

	public function transformValue($value)
	{
		if (! $value)
			$value = 0;

		return number_format(
			$value,
			$this->getDecimals(),
			$this->decimalSeparator,
			$this->thousandsSeparator
		);
	}
}
