<?php

namespace ilBronza\Datatables\DatatablesFields\Dates;

use ilBronza\Datatables\DatatablesFields\DatatableField;

class DatatableFieldCarbon extends DatatableField
{
    public $defaultFilterType = 'date';

    public function transformValue($value)
    {
        return $value->timestamp ?? null;
    }    
}