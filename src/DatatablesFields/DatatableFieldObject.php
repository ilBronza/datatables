<?php

namespace IlBronza\Datatables\DatatablesFields;

class DatatableFieldObject extends DatatableField
{
	public function transformValue($value)
	{
		return json_encode($value);
	}	
}