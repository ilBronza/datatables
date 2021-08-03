<?php

namespace IlBronza\Datatables\DatatablesFields\Notifications;

use IlBronza\Datatables\DatatablesFields\DatatableField;

class DatatableFieldNotification extends DatatableField
{
	public $requireElement = true;

	public function transformValue($value)
	{
		if(! $value)
			return ;

		$notificationType = $value->getType();

		$notificationClassName = "IlBronza\Notifications\DatatablesFields\DatatableFieldNotification" . $notificationType;

		if(class_exists($notificationClassName))
			return $notificationClassName::_transformValue($value);

		if($message = $value->getMessage())
			return $message;

		return 'Manage notification field type: ' . $notificationType;
	}
}