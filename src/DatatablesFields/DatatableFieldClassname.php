<?php

namespace IlBronza\Datatables\DatatablesFields;

class DatatableFieldClassname extends DatatableField
{
    public function transformValue($value)
    {
    	if(! $value)
    		return ;

        return get_class($value);
    }
}