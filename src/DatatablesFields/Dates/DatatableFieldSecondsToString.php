<?php

namespace IlBronza\Datatables\DatatablesFields\Dates;

use IlBronza\Datatables\DatatablesFields\DatatableField;

class DatatableFieldSecondsToString extends DatatableField
{
	public $showSeconds = true;
	public $showZeros = false;

	public function getCustomColumnDefSingleResult()
	{
		$secondsString = ($this->showSeconds)? "item += seconds + '\"';" : "";
		$showZerosString = ($this->showZeros)? "" : "&&(item > 0)";

		return "
			if((Number.isInteger(item))" . $showZerosString . ")
			{
				let hours = Math.floor(item / 3600);
				let minutes = Math.floor((item % 3600) / 60);
				let seconds = item % 60;

				item = '';

				if(hours > 0)
					item += hours + 'h ';

				if((hours > 0)||(minutes > 0))
					item += minutes + '\' ';

				" . $secondsString . "

				item = '<nobr>' + item + '</nobr>';
			}

			else item = ''";
	}
}