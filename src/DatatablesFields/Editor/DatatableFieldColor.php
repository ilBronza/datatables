<?php

namespace IlBronza\Datatables\DatatablesFields\Editor;

use IlBronza\Datatables\DatatablesFields\FieldTypesTraits\EditorSingleFieldTrait;

class DatatableFieldColor extends DatatableFieldEditor
{
	use EditorSingleFieldTrait;

	public $width = '25px';
	public $fieldType = 'color';
	public $htmlClasses = [
		'ib-editor-color'
	];

	public function getCustomColumnDefSingleResult()
	{
		return "
		" . $this->substituteUrlParameter() . "

		item = '<input " . $this->getHtmlClassesAttributeString() . $this->getHtmlDataAttributesString() . $this->getTypeString() . $this->getValueString() . " />';
		";
	}
}