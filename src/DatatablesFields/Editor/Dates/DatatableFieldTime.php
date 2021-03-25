<?php

namespace IlBronza\Datatables\DatatablesFields\Editor\Dates;

use IlBronza\Datatables\DatatablesFields\Editor\Dates\DatatableFieldDate;

class DatatableFieldTime extends DatatableFieldDate
{

	public $width = '55px';
    public $inputFieldDefaultFormat = "HH:mm";
	public $fieldType = 'time';
}