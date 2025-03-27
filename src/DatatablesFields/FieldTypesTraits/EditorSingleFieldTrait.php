<?php

namespace IlBronza\Datatables\DatatablesFields\FieldTypesTraits;

trait EditorSingleFieldTrait
{
	public function getEditorFieldType()
	{
		return $this->fieldType;
	}

	public function getValueString()
	{
		return " data-originalvalue=\"' + item[1] + '\" value=\"' + item[1] + '\" ";
	}

	public function getTypeString()
	{
		return " type=\"" . $this->getEditorFieldType() . "\" ";
	}

	public function returnFlat()
	{
		return "

            if(item[1] === null)
                item[1] = '';

        item = '<span>' + item[1] + '</span>';

        ";
	}

	public function getCustomColumnDefSingleResult()
	{
		if (! $this->userCanEdit())
			return $this->returnFlat();

		$classes = $this->getHtmlClassesString();

		return "

        " . $this->substituteUrlParameter() . "

        item = '<input " . $this->getHtmlDataAttributesString() . $this->getValueString() . $this->getTypeString() . " class=\"" . $classes . " uk-input ib-editor-text\" data-url=\"' + url + '\" data-field=\"{$this->parameter}\" />';

        ";
	}
}

