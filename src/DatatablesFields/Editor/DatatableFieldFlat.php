<?php

namespace IlBronza\Datatables\DatatablesFields\Editor;

use IlBronza\Datatables\DatatablesFields\FieldTypesTraits\EditorSingleFieldTrait;

class DatatableFieldFlat extends DatatableFieldEditor
{
	use EditorSingleFieldTrait;

	public $fieldType = 'text';
}