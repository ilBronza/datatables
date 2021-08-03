<?php

namespace IlBronza\Datatables\DatatablesFields\Notifications;

use IlBronza\Datatables\DatatablesFields\DatatableField;

class DatatableFieldType extends DatatableField
{
	public function transformValue($value)
	{
		if(! $value)
			return ;

		$parts = explode("\\", $value);

		return __('notifications.type' . array_pop($parts));
	}
}