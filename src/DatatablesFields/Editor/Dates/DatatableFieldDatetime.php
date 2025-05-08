<?php

namespace IlBronza\Datatables\DatatablesFields\Editor\Dates;

use IlBronza\Datatables\DatatablesFields\Editor\Dates\DatatableFieldDate;

class DatatableFieldDatetime extends DatatableFieldDate
{

	public $width = '12em';
    public $inputFieldDefaultFormat = "YYYY-MM-DD[T]HH:mm:ss";
	public $fieldType = 'datetime-local';
}