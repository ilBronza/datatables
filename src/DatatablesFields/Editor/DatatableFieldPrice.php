<?php

namespace IlBronza\Datatables\DatatablesFields\Editor;

class DatatableFieldPrice extends DatatableFieldNumeric
{
	public ? string $textAlign = 'right';
	public $digits = 2;

	public $width = '5em';
}