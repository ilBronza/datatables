<?php

namespace IlBronza\Datatables\DatatablesFields\Editor;

use IlBronza\Datatables\DatatablesFields\FieldTypesTraits\ConditionallyDisabledEditorTrait;

/**
 * Select editor disabilitabile in base al model della singola riga.
 */
class DatatableFieldSelectConditionallyDisabled extends DatatableFieldSelect
{
	use ConditionallyDisabledEditorTrait;

	protected function getConditionallyDisabledCellIndex() : int
	{
		return 3;
	}

	protected function getConditionallyDisabledHtmlTag() : string
	{
		return 'select';
	}
}
