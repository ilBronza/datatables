<?php

namespace IlBronza\Datatables\DatatablesFields\Editor;

use IlBronza\Datatables\DatatablesFields\FieldTypesTraits\EditorSingleFieldTrait;

class DatatableFieldFlat extends DatatableFieldEditor
{
	use EditorSingleFieldTrait;

	public $fieldType = 'text';

	public function getCustomColumnDefSingleResult()
	{

		// $asd = "
		// 	item = '<span " . $this->getHtmlClassesAttributeString() . $iconString . " ></span>';
		// ";


		$classes = $this->getHtmlClassesString();

		return "

		" . $this->substituteUrlParameter() . "

		item = '<input " . $this->getHtmlDataAttributesString() . $this->getValueString() . $this->getTypeString() . " class=\"" . $classes . " uk-input ib-editor-text\" data-url=\"' + url + '\" data-field=\"{$this->parameter}\" />';

		";
	}
}