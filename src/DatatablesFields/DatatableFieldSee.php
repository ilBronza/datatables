<?php

namespace IlBronza\Datatables\DatatablesFields;

class DatatableFieldSee extends DatatableFieldLink
{
    public function transformValue($value)
    {
        return $value->getShowUrl();
    }
}
