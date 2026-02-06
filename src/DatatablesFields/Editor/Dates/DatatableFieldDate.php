<?php

namespace IlBronza\Datatables\DatatablesFields\Editor\Dates;

use IlBronza\Datatables\DatatablesFields\Editor\DatatableFieldEditor;
use IlBronza\Datatables\DatatablesFields\FieldTypesTraits\CarbonTrait;
use IlBronza\Datatables\DatatablesFields\FieldTypesTraits\EditorSingleFieldTrait;

class DatatableFieldDate extends DatatableFieldEditor
{
	use EditorSingleFieldTrait;
	use CarbonTrait;

	public $defaultWidth = '8em';
	public $width = '8em';
	public $inputFieldDefaultFormat = "YYYY-MM-DD";
	public $fieldType = 'date';

	public function transformValue($value)
	{
		if (! $this->requireElement())
			return $value;

		$this->element = $value;

		return [
			$this->element->getKey(),
			$value->{$this->name}->timestamp ?? null
		];
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