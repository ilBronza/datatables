<?php

namespace IlBronza\Datatables\DatatablesFields\Flats\Dates;

use IlBronza\Datatables\DatatablesFields\Dates\DatatableFieldCarbon;

class DatatableFieldDate extends DatatableFieldCarbon
{
    public $dateFormat = "d-m-Y";

    public function transformValue($value)
    {
		if (! $value)
			return $this->transformValueWithValidity(null, null);

        return $this->transformValueWithValidity($value->format($this->dateFormat), $value);
    }
}
