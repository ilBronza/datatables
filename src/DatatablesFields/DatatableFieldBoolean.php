<?php

namespace IlBronza\Datatables\DatatablesFields;

class DatatableFieldBoolean extends DatatableField
{
	public function transformValue($value)
	{
		if(is_null($value))
			return ;

		return !! $value;
	}

    public function getColumnDefSingleResult()
    {
    	return "
			if(item === true)
				item = '" . trans('fields.booleanTrue') . "';

			else if(item === false)
				item = '" . trans('fields.booleanFalse') . "';

			else
				item =  '" . trans('fields.booleanNull') . "';
    	";
    }
}