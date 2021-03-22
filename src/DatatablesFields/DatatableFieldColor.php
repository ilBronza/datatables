<?php

namespace IlBronza\Datatables\DatatablesFields;

class DatatableFieldColor extends DatatableField
{
	public $width = '25px';

    public function getCustomColumnDefSingleResult()
    {
        return "
            if(item)
				item = '<div style=\"width:" . $this->width . "; background-color:' + item + ';\" >&nbsp;</div>';

            else item = ''
		";
    }
}


						return '';
