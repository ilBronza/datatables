<?php

namespace IlBronza\Datatables\DatatablesFields\Iterators;

class DatatableFieldLast extends DatatableFieldFirst
{
	public function transformValue($value)
	{
		if(! ($item = $value->last()))
			return ;

		//return first element of array, object, collection
		return $this->getItemValue($item);
	}
}