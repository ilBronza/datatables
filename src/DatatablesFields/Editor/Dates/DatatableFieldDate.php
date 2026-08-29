<?php

namespace IlBronza\Datatables\DatatablesFields\Editor\Dates;

use IlBronza\Datatables\DatatablesFields\Editor\DatatableFieldEditor;
use IlBronza\Datatables\DatatablesFields\FieldTypesTraits\CarbonTrait;
use IlBronza\Datatables\DatatablesFields\FieldTypesTraits\EditorSingleFieldTrait;

class DatatableFieldDate extends DatatableFieldEditor
{
	use EditorSingleFieldTrait;
	use CarbonTrait;

	public ?string $inlineFieldType = 'editor.dates.date';
	public bool $requiresRowSelectCheckbox = true;

	public $defaultWidth = '8em';
	public $width = '8em';
	public $inputFieldDefaultFormat = "YYYY-MM-DD";
	public $fieldType = 'date';
	public $defaultFilterType = 'date';
	public bool $checkValidity = false;
	public null|int|string $validityPosition = 2;

	public function isBulkEditable() : bool
	{
		return true;
	}

	public function transformValue($value)
	{
		if (! $this->requireElement())
			return $value;

		$this->element = $value;
		$date = $value->{$this->name} ?? null;

		$result = [
			$this->element->getKey(),
			$date->timestamp ?? null
		];

		if ($this->checkValidity)
			$result[] = $date ? ($date->isPast() ? 0 : 1) : null;

		return $result;
	}

	public function setParameters(array $parameters)
	{
		parent::setParameters($parameters);

		if (! $this->checkValidity)
			return;

		$this->valueAsRowClass = true;
		$this->valueAsRowClassPrefix = true;
	}

	public function getValueAsRowClassDataIndexString() : ?string
	{
		if ($this->checkValidity)
			return "[{$this->validityPosition}]";

		return parent::getValueAsRowClassDataIndexString();
	}

	public function getInlineEditValue() : string
	{
		$property = $this->editorProperty ?? $this->name;
		$value = $this->element->{$property} ?? null;

		if (! $value)
			return '';

		if (method_exists($value, 'format'))
			return $value->format('Y-m-d');

		return (string) $value;
	}

	public function getCustomColumnDefSingleResult()
	{
		if (! $this->userCanEdit())
			return "

            if(item[1])
            {
                let date = moment.unix(item[1]);

                if(date.isValid())
                    item = date.format('" . $this->inputFieldDefaultFormat . "');
            }

        ";

		$classes = $this->getHtmlClassesString();

		return "

		" . $this->substituteUrlParameter() . "

		if(item[1])
		{
			let date = moment.unix(item[1]);

			if(date.isValid())
				item[1] = date.format('" . $this->getInputFieldDefaultDateFormat() . "');
		}

		item = '<input " . $this->getValueString() . $this->getTypeString() . " class=\"" . $classes . " uk-input ib-editor-text ' + ((item[1])? 'ib-compiled' : '') + '\" data-url=\"' + url + '\" data-field=\"{$this->parameter}\" />';

		";
	}
}
