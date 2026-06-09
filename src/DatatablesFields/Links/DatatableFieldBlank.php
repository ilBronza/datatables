<?php

namespace IlBronza\Datatables\DatatablesFields\Links;

use IlBronza\Datatables\DatatablesFields\Links\DatatableFieldLink;

class DatatableFieldBlank extends DatatableFieldLink
{
	public $target = '_blank';

	public function transformValue($value)
	{
		return [
			$value,
			$value,
		];
	}

	public function getLinkTextString()
	{
		return "((item[1])? item[1] : '' )";
	}

	public function getLinkUrlString()
	{
		return "item[0]";
	}
}
