<?php

namespace IlBronza\Datatables\DatatablesFields\Links;

class DatatableFieldSeen extends DatatableFieldAjax
{
    public $icon = 'check';
    public $textParameter = false;
	public $dataAttributes = [
		'type' => 'POST'
	];

	public function transformValue($value)
	{
		return $value->getSeenUrl();
	}
}
