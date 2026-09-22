<?php

namespace IlBronza\Datatables\DatatablesFields\Editor;

use IlBronza\Datatables\DatatablesFields\FieldTypesTraits\ConditionallyDisabledEditorTrait;

/**
 * Select o input disabilitabile in base al model della singola riga.
 */
class DatatableFieldSelectOrInputConditionallyDisabled extends DatatableFieldSelectOrInput
{
	use ConditionallyDisabledEditorTrait;

	protected function getConditionallyDisabledCellIndex() : int
	{
		return 5;
	}

	protected function getConditionallyDisabledHtmlTag() : string
	{
		return 'select';
	}

	protected function getConditionallyDisabledDisplayTags() : array
	{
		return ['select', 'input'];
	}
}
