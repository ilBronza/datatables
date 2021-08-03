<?php

namespace IlBronza\Datatables\DatatablesFields;

class DatatableFieldClassBasename extends DatatableField
{
    public function transformValue($value)
    {
    	if(! $value)
    		return ;

        return class_basename($value);
    }
}