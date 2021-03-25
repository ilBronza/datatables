<?php

namespace IlBronza\Datatables\DatatablesFields\Dates;

use IlBronza\Datatables\DatatablesFields\DatatableField;
use IlBronza\Datatables\DatatablesFields\FieldTypesTraits\CarbonTrait;

class DatatableFieldCarbon extends DatatableField
{
    use CarbonTrait;

    public $defaultFilterType = 'date';
    public $defaultWidth = '80px';

    public function transformValue($value)
    {
        return $value->timestamp ?? null;
    }
}