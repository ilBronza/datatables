<?php

namespace IlBronza\Datatables\DatatablesFields\Numbers;

use IlBronza\Datatables\DatatablesFields\DatatableField;
use IlBronza\Datatables\DatatablesFields\FieldTypesTraits\DecimalsTrait;

class DatatableFieldPercentage extends DatatableFieldPrice
{
	public $suffix = ' %';
}