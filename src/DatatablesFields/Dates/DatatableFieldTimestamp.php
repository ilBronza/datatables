<?php

namespace IlBronza\Datatables\DatatablesFields\Dates;

class DatatableFieldTimestamp extends DatatableFieldCarbon
{
	public $defaultWidth = '5em';
	public $width = '6em';

    public function getCustomColumnDefSingleResult()
    {
		return " ";
    }

    public function getCustomColumnDefSingleSearchResult()
    {
	    return ' ';
    }
}

