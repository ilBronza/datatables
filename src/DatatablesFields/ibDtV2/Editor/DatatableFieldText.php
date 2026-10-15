<?php

namespace IlBronza\Datatables\DatatablesFields\ibDtV2\Editor;

use IlBronza\Datatables\DatatablesFields\ibDtV2\Traits\InputControlTrait;

class DatatableFieldText extends DatatableFieldEditor
{
	use InputControlTrait;

	public $fieldType = 'text';
	public string $editorValueType = 'text';

	public function getWidthFromConfig() : string
	{
		return config('datatables.datatableFieldWidths.ibDtV2.editor.datatableFieldText', '125px');
	}
}
