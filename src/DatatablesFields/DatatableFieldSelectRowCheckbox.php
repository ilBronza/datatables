<?php

namespace IlBronza\Datatables\DatatablesFields;

class DatatableFieldSelectRowCheckbox extends DatatableField
{
	public ?string $translationPrefix = 'datatables::fields';

	/**
	 * Larga abbastanza da ospitare checkbox e caret del dropdown
	 * senza click accidentali sul select-all.
	 */
	public $width = '46px';
	public $filterable = false;
	public $showLabel = false;

	public function transformValue($value)
	{
		return;
	}
}