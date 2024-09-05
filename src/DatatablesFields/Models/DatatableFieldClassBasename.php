<?php

namespace IlBronza\Datatables\DatatablesFields\Models;

use IlBronza\Datatables\DatatablesFields\DatatableField;

class DatatableFieldClassBasename extends DatatableField
{
    public function transformValue($value)
    {
    	if(! $value)
    		return ;

        return class_basename($value);
    }
}