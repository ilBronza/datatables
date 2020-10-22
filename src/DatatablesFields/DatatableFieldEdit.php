<?php

namespace IlBronza\Datatables\DatatablesFields;

class DatatableFieldEdit extends DatatableFieldLink
{
	public $icon = 'file-edit';

	public function transformValue($value)
	{
		return $value->getEditUrl();
	}
}
