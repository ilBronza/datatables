<?php

namespace IlBronza\Datatables\DatatablesFields\Editor;

use IlBronza\Datatables\DatatablesFields\FieldTypesTraits\ConditionallyDisabledEditorTrait;

/**
 * Text editor disabilitabile in base al model della singola riga.
 */
class DatatableFieldTextConditionallyDisabled extends DatatableFieldText
{
	use ConditionallyDisabledEditorTrait;

	protected function getConditionallyDisabledCellIndex() : int
	{
		return 2;
	}

	protected function getConditionallyDisabledHtmlTag() : string
	{
		return 'input';
	}
}
