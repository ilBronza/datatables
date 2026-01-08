<?php

namespace IlBronza\Datatables\DatatablesFields;

class DatatableFieldSelectRowCheckbox extends DatatableField
{
	public ?string $translationPrefix = 'datatables::fields';
	public $width = '20px';
	public $filterable = false;
	public $showLabel = false;

	public function transformValue($value)
	{
		return;
	}
}