<?php

namespace IlBronza\Datatables\DatatablesFields;

class DatatableFieldIcon extends DatatableField
{
	public function getCustomColumnDefSingleResult()
	{
		return "

			if(item)
				item = '<i class=\"fa-solid fa-' + item + '\"></i>';

			else item = '';
		";
	}
}