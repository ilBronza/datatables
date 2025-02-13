<?php

namespace IlBronza\Datatables\DatatablesFields;

class DatatableFieldFlat extends DatatableField
{
	public null|int|string $keyPosition = null;
	public null|int|string $valuePosition = null;

	public function transformValue($value)
	{
		return $value;
	}
}