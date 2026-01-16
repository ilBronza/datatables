<?php

namespace IlBronza\Datatables\DatatablesFields\Editor;

use IlBronza\Datatables\DatatablesFields\FieldTypesTraits\EditorSingleFieldTrait;

class DatatableFieldColor extends DatatableFieldEditor
{
	use EditorSingleFieldTrait;

	public $width = '65px';
	public $fieldType = 'color';
	public $htmlClasses = [
		'ib-editor-color'
	];

	public ? string $translationPrefix = 'datatables::fields';
	public ? string $forcedStandardName = 'color';

	public function getCustomColumnDefSingleResult()
	{
        if(! $this->userCanEdit())
        	return $this->returnFlat();

		return "
		" . $this->substituteUrlParameter() . "

		item = '<input " . $this->getHtmlClassesAttributeString() . $this->getHtmlDataAttributesString() . $this->getTypeString() . $this->getValueString() . " />';
		";
	}
}