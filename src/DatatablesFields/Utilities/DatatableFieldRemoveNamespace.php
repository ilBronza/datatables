<?php

namespace IlBronza\Datatables\DatatablesFields\Utilities;

use IlBronza\Datatables\DatatablesFields\DatatableFieldFlat;

class DatatableFieldRemoveNamespace extends DatatableFieldFlat
{
	public function transformValue($value)
	{
		$pieces = explode("\\", $value);

		return array_pop($pieces);
	}
}