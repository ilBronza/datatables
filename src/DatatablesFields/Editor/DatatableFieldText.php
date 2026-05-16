<?php

namespace IlBronza\Datatables\DatatablesFields\Editor;

use IlBronza\Datatables\DatatablesFields\FieldTypesTraits\EditorSingleFieldTrait;

class DatatableFieldText extends DatatableFieldEditor
{
	use EditorSingleFieldTrait;

	public bool $requiresRowSelectCheckbox = true;

	public $width = '125px';
	public $fieldType = 'text';

	public function isBulkEditable() : bool
	{
		return true;
	}

}