<?php

namespace IlBronza\Datatables\DatatablesFields\Dates;

use IlBronza\Datatables\DatatablesFields\DatatableField;

class DatatableFieldDaysToString extends DatatableField
{
	public $showZeros = true;

	public $width = '4em';

    public function getCustomColumnDefSingleSearchResult()
    {
		return "";
    }

	public function getCustomColumnDefSingleResult()
	{
		$showZerosString = ($this->showZeros)? "" : "&&(item > 0)";

		return "
			if((item)" . $showZerosString . ")
				item = ((item < 0)? '-' : '') +  Math.floor(Math.abs(item)) + 'd ' + Math.floor(Math.abs((item % 1) * 8)) + 'h' ;

			else item = ''";
	}
}