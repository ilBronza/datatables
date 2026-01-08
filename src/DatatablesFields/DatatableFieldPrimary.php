<?php

namespace IlBronza\Datatables\DatatablesFields;

class DatatableFieldPrimary extends DatatableField
{
	public ?string $translationPrefix = 'datatables::fields';
	public $requireElement = true;
	public $rowId = true;
	public $visible = false;

	public $width = '12em';

	public function transformValue($value)
	{
		if(! $value)
			return ;

		return $value->getKey();
	}
}