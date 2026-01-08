<?php

namespace IlBronza\Datatables\DatatablesFields;

class DatatableFieldFlatColor extends DatatableFieldFlat
{
	public function getCustomColumnDefSingleResult()
	{
		return "
            if(item[0])
				item = '<div style=\"background-color:' + item[1] + ';\" >' + item[0] + '</div>';

            else item = ''
		";
	}
}