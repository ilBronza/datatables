<?php

namespace IlBronza\Datatables\DatatablesFields\Dates\FromString;

use Carbon\Carbon;
use IlBronza\Datatables\DatatablesFields\Dates\DatatableFieldDate as MainDatatableFieldDate;

class DatatableFieldDate extends MainDatatableFieldDate
{
	public function transformValue($value)
	{
		if(! $value)
			return null;

		$value = Carbon::createFromFormat('Y-m-d', $value);

		return $value->timestamp ?? null;
	}

}

