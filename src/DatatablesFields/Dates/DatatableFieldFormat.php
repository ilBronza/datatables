<?php

namespace IlBronza\Datatables\DatatablesFields\Dates;

class DatatableFieldFormat extends DatatableFieldCarbon
{
    public $format = "d-m-y";

    public function transformValue($value)
    {
        if($value)
            return $value->format($this->format);
    }
}

