<?php

namespace IlBronza\Datatables\DatatablesFields\Links;

use IlBronza\Datatables\DatatablesFields\Links\DatatableFieldIconLink;

class DatatableFieldSeeName extends DatatableFieldTextLink
{
    public function transformValue($value)
    {
        return [
        	$value->getShowUrl(),
        	$value->getName()
        ];
    }
}
