<?php

namespace IlBronza\Datatables\DatatablesFields\Editor;

use IlBronza\Datatables\DatatablesFields\FieldTypesTraits\EditorSingleFieldTrait;

class DatatableFieldTextLegacy extends DatatableFieldEditor
{
	use EditorSingleFieldTrait;

	public bool $requiresRowSelectCheckbox = true;

	public $width = '125px';
	public $fieldType = 'text';

	public function isBulkEditable() : bool
	{
		return true;
	}

	public function getWidthFromConfig() : string
	{
		if (static::class !== self::class)
			return parent::getWidthFromConfig();

		// Reuse existing published text widths until a legacy override is set.
		return config(
			'datatables.datatableFieldWidths.editor.datatableFieldTextLegacy',
			config('datatables.datatableFieldWidths.editor.datatableFieldText', 'auto')
		);
	}
}
