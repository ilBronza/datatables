<?php

namespace IlBronza\Datatables\DatatablesFields\Dates;

class DatatableFieldDatetime extends DatatableFieldDate
{
    public $dateFormat = "D/MM/YYYY HH:mm";
	public $width = '8em';

	public function transformValue($value)
	{
		if(! $value)
			return null;

		return $value->timestamp;
//
//		$date = $value->format('Y-m-d'); // QUI: prendi il giorno "di calendario" che vuoi preservare
//
//		return \Carbon\Carbon::createFromFormat('Y-m-d', $date, 'UTC')
//			->timestamp;
	}

}

