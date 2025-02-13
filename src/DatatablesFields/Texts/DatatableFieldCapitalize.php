<?php

namespace IlBronza\Datatables\DatatablesFields\Texts;

use IlBronza\Datatables\DatatablesFields\DatatableFieldFlat;

use function strtolower;
use function ucfirst;

class DatatableFieldCapitalize extends DatatableFieldFlat
{
	public function transformValue($value)
	{
		return ucfirst(strtolower($value));
	}
}