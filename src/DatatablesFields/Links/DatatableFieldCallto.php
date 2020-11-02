<?php

namespace IlBronza\Datatables\DatatablesFields\Links;

class DatatableFieldCallto extends DatatableFieldLink
{
	public $icon = 'receiver';

	public function getLinkColumnDefResult()
	{
		return "'<a href=\"tel:' + data + '\" ><span uk-icon=\"" . $this->icon . "\"></span> ' + data + '</a>'";
	}
}