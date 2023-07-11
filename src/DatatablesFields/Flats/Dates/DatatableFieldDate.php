<?php

namespace IlBronza\Datatables\DatatablesFields\Flats\Dates;

use IlBronza\Datatables\DatatablesFields\Dates\DatatableFieldCarbon;

class DatatableFieldDate extends DatatableFieldCarbon
{
    public $dateFormat = "d-m-Y";

    public function transformValue($value)
    {
        return $value->format($this->dateFormat);
    }
}

