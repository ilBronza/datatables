<?php

namespace IlBronza\Datatables\DatatablesFields\Dates;

use IlBronza\Datatables\DatatablesFields\DatatableField;

class DatatableFieldSecondsToMinutes extends DatatableField
{
	public $showZeros = false;

    public function getCustomColumnDefSingleSearchResult()
    {
		$showZerosString = ($this->showZeros)? "" : "&&(item > 0)";

		return "
			if((Number.isInteger(item))" . $showZerosString . ")
				item = Math.floor(item / 60);

			else item = ''";
    }

	public function getCustomColumnDefSingleResult()
	{
		$showZerosString = ($this->showZeros)? "" : "&&(item > 0)";

		return "
			if((Number.isInteger(item))" . $showZerosString . ")
				item = Math.floor(item / 60) + '\'';

			else item = ''";
	}
}