<?php

namespace IlBronza\Datatables\DatatablesFields;

class DatatableFieldInteger extends DatatableField
{
	public function transformValue($value)
	{
		if(! $value)
			return ;

		return (int) $value;
	}
}