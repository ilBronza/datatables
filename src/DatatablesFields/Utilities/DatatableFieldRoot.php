<?php

namespace IlBronza\Datatables\DatatablesFields\Utilities;

use IlBronza\Datatables\DatatablesFields\DatatableFieldFlat;

class DatatableFieldRoot extends DatatableFieldFlat
{
	public function transformValue($value)
	{
		return $this->parent_id ?? false;
	}
}