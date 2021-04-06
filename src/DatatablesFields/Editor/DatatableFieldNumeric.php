<?php

namespace IlBronza\Datatables\DatatablesFields\Editor;

use IlBronza\Datatables\DatatablesFields\FieldTypesTraits\EditorSingleFieldTrait;

class DatatableFieldNumeric extends DatatableFieldEditor
{
	use EditorSingleFieldTrait;

	//datatable type to be sorted in datatable
	public $datatableType = 'numeric';
	public $fieldType = 'numeric';

	public $width = '65px';

	public function getCustomColumnDefSingleResult()
	{
		$classes = $this->getHtmlClassesString();

		return "

		" . $this->substituteUrlParameter() . "

		item = '<input " . $this->getValueString() . $this->getTypeString() . " class=\"" . $classes . " uk-input ib-editor-text\" data-url=\"' + url + '\" data-field=\"{$this->parameter}\" />';

		";
	}
}