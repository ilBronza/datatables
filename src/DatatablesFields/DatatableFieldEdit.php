<?php

namespace IlBronza\Datatables\DatatablesFields;

use IlBronza\Datatables\DatatablesFields\Links\DatatableFieldIconLink;

class DatatableFieldEdit extends DatatableFieldIconLink
{
	public $icon = 'file-edit';

	public function transformValue($value)
	{
		return $value->getEditUrl();
	}
}
