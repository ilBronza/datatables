<?php

namespace IlBronza\Datatables\DatatablesFields;

class DatatableFieldBooleanAlarm extends DatatableFieldBoolean
{
	protected function getBooleanString(string $iconString)
	{
		return "
			item = '<i class=\"ib-alarm fa-solid fa-{$iconString}\"></i>';
		";
	}
}