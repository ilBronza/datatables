<?php

namespace IlBronza\Datatables\DatatablesFields;

class DatatableFieldProperty extends DatatableField
{
	public $property;
	// public $requiresPlaceholderElement = true;

	public function transformValue($value)
	{
		if(! $value)
			return ;

		return $value->{$this->property};
	}
}