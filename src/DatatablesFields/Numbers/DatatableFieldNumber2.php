<?php

namespace IlBronza\Datatables\DatatablesFields\Numbers;

class DatatableFieldNumber2 extends DatatableFieldPrice
{
	public ?string $suffix = null;

	public function transformValue($value)
	{
		if (! $value)
			return;

		return $value;
	}

	public function getExportResultOptionsEditor()
	{
		return "";
	}

}