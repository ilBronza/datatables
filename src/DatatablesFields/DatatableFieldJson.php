<?php

namespace IlBronza\Datatables\DatatablesFields;

class DatatableFieldJson extends DatatableField
{
    public function transformValue($value)
    {
    	if(! $value)
    		return ;

        return json_encode($value);
    }
}