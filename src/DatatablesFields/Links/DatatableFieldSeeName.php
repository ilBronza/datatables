<?php

namespace IlBronza\Datatables\DatatablesFields\Links;

use IlBronza\Datatables\DatatablesFields\Links\DatatableFieldIconLink;

class DatatableFieldSeeName extends DatatableFieldIconLink
{
	public $textParameter = 'name';

    public function transformValue($value)
    {
        return [
        	$value->getShowUrl(),
        	$value->getName()
        ];
    }
}
