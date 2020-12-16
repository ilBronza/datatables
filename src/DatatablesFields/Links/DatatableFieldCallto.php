<?php

namespace IlBronza\Datatables\DatatablesFields\Links;

class DatatableFieldCallto extends DatatableFieldLink
{
	public $icon = 'receiver';
	public $field = 'cellualre';

	public function transformValue($value)
	{
		$phoneNumber = $value->{$this->field};
		$id = $value->getKey();

		return [$id => $phoneNumber];
	}

	public function getLinkColumnDefResult()
	{
		return "'<a data-id=\"' + Object.keys(data)[0] + '\" class=\"callto\" href=\"tel:' + data[Object.keys(data)[0]] + '\" >' + data[Object.keys(data)[0]] + '</a>'";
	}
}