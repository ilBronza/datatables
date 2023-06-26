<?php

namespace IlBronza\Datatables\DatatablesFields;

class DatatableFieldGetType extends DatatableField
{
    public function transformValue($value)
    {
    	if(! $value)
    		return ;

        return gettype($value);
    }
}