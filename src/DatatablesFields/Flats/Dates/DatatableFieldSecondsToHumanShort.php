<?php

namespace IlBronza\Datatables\DatatablesFields\Flats\Dates;

use Carbon\CarbonInterval;
use IlBronza\Datatables\DatatablesFields\DatatableField;

class DatatableFieldSecondsToHumanShort extends DatatableField
{
    public function transformValue($value)
    {
        $result = CarbonInterval::seconds($value)->cascade()->forHumans(null, true);

        $result = str_replace(" min.", "&prime;", $result);
        $result = str_replace(" sec.", "&Prime;", $result);

        return $result;
    }
}

