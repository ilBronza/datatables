<?php

namespace IlBronza\Datatables\DatatablesFields;

class DatatableFieldFlatColorClass extends DatatableFieldFlat
{
	public function getCustomColumnDefSingleResult()
	{
		return "
            if(item[0])
				item = '<div class=\"ib-td-color ' + item[1] + '\" >' + item[0] + '</div>';

            else item = ''
		";
	}
}