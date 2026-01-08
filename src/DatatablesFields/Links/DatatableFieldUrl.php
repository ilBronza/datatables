<?php

namespace IlBronza\Datatables\DatatablesFields\Links;

class DatatableFieldUrl extends DatatableFieldLink
{
	public $faIcon = 'link';

	public function transformValue($value)
	{
		return $value;
	}
}
