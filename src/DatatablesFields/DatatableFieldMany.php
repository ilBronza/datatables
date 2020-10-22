<?php

namespace IlBronza\Datatables\DatatablesFields;

class DatatableFieldMany extends MultipleDatatableField
{
	public function getItemValue($item)
	{
		return [
			$item->getShowUrl(),
			$item->getNameForDisplayRelation()
		];
	}

	public function getColumnDefSingleResult()
	{
		return "'<a href=\"' + item[0] + '\" >' + item[1] + '</a>" . $this->getSeparator() . "';";
	}
}