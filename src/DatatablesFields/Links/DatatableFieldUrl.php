<?php

namespace IlBronza\Datatables\DatatablesFields\Links;

class DatatableFieldUrl extends DatatableFieldLink
{
	public $icon = 'link';

	public function transformValue($value)
	{
		return $value;
	}
}
