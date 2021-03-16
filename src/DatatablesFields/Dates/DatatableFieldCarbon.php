<?php

namespace IlBronza\Datatables\DatatablesFields\Dates;

use IlBronza\Datatables\DatatablesFields\DatatableField;

class DatatableFieldCarbon extends DatatableField
{
	public $defaultWidth = '80px';
    public $defaultFilterType = 'date';

    public function getDateFormat()
    {
    	return $this->dateFormat;
    }

    public function transformValue($value)
    {
        return $value->timestamp ?? null;
    }
}