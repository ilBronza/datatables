<?php

namespace IlBronza\Datatables\DatatablesFields\Iterators;

class DatatableFieldFirst extends DatatableFieldSingle
{
	public function transformValue($value)
	{
		if(! ($item = $value->first()))
			return ;

		//return first element of array, object, collection
		return $this->getItemValue($item);
	}
}