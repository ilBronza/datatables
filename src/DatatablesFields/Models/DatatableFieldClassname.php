<?php

namespace IlBronza\Datatables\DatatablesFields\Models;

use IlBronza\Datatables\DatatablesFields\DatatableField;

class DatatableFieldClassname extends DatatableField
{
    public function transformValue($value)
    {
    	if(! $value)
    		return ;

        return get_class($value);
    }
}