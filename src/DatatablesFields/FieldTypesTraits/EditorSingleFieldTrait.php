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

	public function getInlineEditValue() : string
	{
		$property = $this->editorProperty ?? $this->name;

		return (string) ($this->element->{$property} ?? '');
	}

	public function getInlineEditHtml() : string
	{
		if (! $this->userCanEdit())
			return '';

		$dataAttributes = $this->getDataAttributes();
		$dataAttributes['url'] = $this->getInlineEditUpdateUrl();

		$attributes = [];

		foreach ($dataAttributes as $name => $value)
			$attributes[] = 'data-' . e($name) . '="' . e($value) . '"';

		$value = e($this->getInlineEditValue());
		$classes = e(trim($this->getHtmlClassesString() . ' uk-input ib-editor-text ib-datatable-inline-edit-field'));

		return '<input ' . implode(' ', $attributes) . ' data-originalvalue="' . $value . '" value="' . $value . '" type="' . e($this->getEditorFieldType()) . '" class="' . $classes . '" />';
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
