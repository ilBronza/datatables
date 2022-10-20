<?php

namespace IlBronza\Datatables\DatatablesFields;

class DatatableFieldNotBoolean extends DatatableFieldBoolean
{
	public function transformValue($value)
	{
		return ! $value;
	}
}