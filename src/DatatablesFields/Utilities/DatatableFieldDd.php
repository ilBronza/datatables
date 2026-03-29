<?php

namespace IlBronza\Datatables\DatatablesFields\Utilities;

use IlBronza\Datatables\DatatablesFields\DatatableFieldFlat;

class DatatableFieldDd extends DatatableFieldFlat
{
	public function transformValue($value)
	{
		dd($value);
	}
}