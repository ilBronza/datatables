<?php

namespace IlBronza\Datatables\DatatablesFields;

use IlBronza\Datatables\DatatablesFields\Links\DatatableFieldIconLink;

class DatatableFieldSee extends DatatableFieldIconLink
{
    public function transformValue($value)
    {
        return $value->getShowUrl();
    }
}
