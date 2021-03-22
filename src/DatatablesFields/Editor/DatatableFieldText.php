<?php

namespace IlBronza\Datatables\DatatablesFields\Editor;

use IlBronza\Datatables\DatatablesFields\FieldTypesTraits\EditorSingleFieldTrait;

class DatatableFieldText extends DatatableFieldEditor
{
	use EditorSingleFieldTrait;

	public $width = '125px';
	public $fieldType = 'text';

	public function getCustomColumnDefSingleResult()
	{
		$classes = $this->getHtmlClassesString();

		return "

		" . $this->substituteUrlParameter() . "

		item = '<input " . $this->getValueString() . $this->getTypeString() . " class=\"" . $classes . " uk-input ib-editor-text\" data-url=\"' + url + '\" data-field=\"{$this->parameter}\" />';

		";
	}
}