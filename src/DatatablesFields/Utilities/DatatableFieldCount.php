<?php

namespace IlBronza\Datatables\DatatablesFields\Utilities;

use IlBronza\Datatables\DatatablesFields\DatatableFieldFlat;

class DatatableFieldCount extends DatatableFieldFlat
{
	public function transformValue($value)
	{
		return count($value);
	}
}