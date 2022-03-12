<?php

namespace IlBronza\Datatables\DatatablesFields\Editor;

use IlBronza\Datatables\DatatablesFields\FieldTypesTraits\EditorSingleFieldTrait;

class DatatableFieldNumeric extends DatatableFieldEditor
{
	use EditorSingleFieldTrait;

	//datatable type to be sorted in datatable
	public $datatableType = 'numeric';
	public $fieldType = 'numeric';
	public $digits = false;

	public $width = '65px';

    public function getValueString()
    {
    	if($this->digits)
        	return " didididi data-originalvalue=\"' + item[1] + '\" value=\"' + ((item[1])? item[1].toFixed({$this->digits}) : '') + '\" ";

        return " data-originalvalue=\"' + item[1] + '\" value=\"' + item[1] + '\" ";
    }

	public function getCustomColumnDefSingleResult()
	{
		$classes = $this->getHtmlClassesString();

		return "

		" . $this->substituteUrlParameter() . "

		item = '<input maruionne " . $this->getHtmlDataAttributesString() . $this->getValueString() . $this->getTypeString() . " class=\"" . $classes . " uk-input ib-editor-text\" data-url=\"' + url + '\" data-field=\"{$this->parameter}\" />';

		";
	}
}