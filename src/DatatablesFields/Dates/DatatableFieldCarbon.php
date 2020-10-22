<?php

namespace IlBronza\Datatables\DatatablesFields\Dates;

use IlBronza\Datatables\DatatablesFields\DatatableField;

class DatatableFieldCarbon extends DatatableField
{
    public $defaultFilterType = 'date';

    public function transformValue($value)
    {
        return $value->timestamp ?? null;
    }
}