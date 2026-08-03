<?php

namespace IlBronza\Datatables\DatatablesFields;

class DatatableFieldFlat extends DatatableField
{
	public null|int|string $keyPosition = null;
	public null|int|string $valuePosition = null;
	public bool $nullLast = false;

	public function hasNullLastOrdering() : bool
	{
		return $this->nullLast || ($this->order['nullLast'] ?? false);
	}

	public function transformValue($value)
	{
		return $value;
	}
}
