<?php

namespace IlBronza\Datatables\DatatablesFields\Numbers;

use IlBronza\Datatables\DatatablesFields\FieldTypesTraits\DecimalsTrait;
use IlBronza\Datatables\DatatablesFields\Numbers\DatatableFieldBaseNumber;

class DatatableFieldFormatted extends DatatableFieldBaseNumber
{
	use DecimalsTrait;

	public $width = '4em';
	public $decimalSeparator = ',';
	public $thousandsSeparator = '.';

	public ?string $textAlign = 'right';

	// public ?string $suffix = ' €';
	public int $decimals = 2;
	public ? string $localeFormatting = null;

	public function getExportResultOptionsEditor()
	{
		return "";
		// return " if(item) item = item.replace('.', ''); ";
	}

	public function getLocaleFormatting()
	{
		if($this->localeFormatting)
			return $this->localeFormatting;

		return 'it-IT';
	}

	public function getCustomColumnDefSingleResult()
	{
		return "
		 	item = Number(item).toLocaleString('{$this->getLocaleFormatting()}', {minimumFractionDigits: {$this->decimals}, maximumFractionDigits: {$this->decimals}, useGrouping: 'always'});
		";		
	}

	public function getCustomColumnDefSingleResultExport()
	{
		return "";
	}

	// public function getCustomColumnDefSingleSortResult()
	// {
	// 	return "
	// 		if(item)
	// 			item.replace('" . $this->thousandsSeparator . "', '').replace('" . $this->suffix . "', '')

	// 			item = 4444;
				
	// 		return item;
	// 	";
	// }

	public function transformValue($value)
	{
		if (! $value)
			return 0;

		return $value;

		// if (! $value)
		// 	return;

		// return number_format(
		// 	$value,
		// 	$this->getDecimals(),
		// 	$this->decimalSeparator,
		// 	$this->thousandsSeparator
		// );
	}
}