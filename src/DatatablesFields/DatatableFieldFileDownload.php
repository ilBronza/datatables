<?php

namespace IlBronza\Datatables\DatatablesFields;

class DatatableFieldFileDownload extends DatatableField
{
	public null|int|string $keyPosition = null;
	public null|int|string $valuePosition = null;

	public function transformValue($value)
	{
		if($value)
			dd($value);

		return $value;
	}
}