<?php

namespace IlBronza\Datatables\DatatablesFields\Flats\Dates;

use Carbon\CarbonInterval;
use IlBronza\Datatables\DatatablesFields\DatatableField;

class DatatableFieldSecondsToHuman extends DatatableField
{
    public function transformValue($value)
    {
        return CarbonInterval::seconds($value)->cascade()->forHumans(null, true);
    }
}

