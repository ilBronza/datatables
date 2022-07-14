<?php

namespace IlBronza\Datatables\DatatablesFields\Editor;

use IlBronza\Datatables\Datatables;
use IlBronza\Datatables\DatatablesFields\FieldTypesTraits\EditorSingleFieldTrait;

class DatatableFieldSelect extends DatatableFieldEditor
{
	use EditorSingleFieldTrait;

	public $width = '125px';
	public $fieldType = 'text';

    public function __construct(string $name, array $parameters = [], int $index = null, DatatableField $parent = null, Datatables $table = null)
	{
		parent::__construct($name, $parameters, $index, $parent, $table);
	}

    public function parseFieldSpecificHeaderData()
    {
		$this->setHeaderDataAttribute(
			'possibleValues',
			json_encode($this->getPossibleEnumValuesArray())
		);        
    }

    public function getPossibleEnumValuesArray()
    {
		if($method = $this->getPossibleValuesMethod())
			if($element = $this->element ?? $this->getPlaceholderElement())
				return $element->{$method}();

        $values = $this->getPossibleEnumValues();

        $result = [];

        foreach($values as $value)
            $result[$value] = $value;

        return $result;
    }

	private function getPossibleValuesMethod()
	{
		return $this->possibleValuesMethod ?? null;
	}

	public function getPossibleEnumValues()
    {
    	$element = $this->element ?? $this->getPlaceholderElement();

        $_enumStr = \DB::select(\DB::raw('SHOW COLUMNS FROM ' . $element->getTable() . ' WHERE Field = "' . $this->name . '"'));

        $enumStr = $_enumStr[0]->Type;
        preg_match_all("/'([^']+)'/", $enumStr, $matches);

        return $matches[1] ?? [];
    }


	public function getCustomColumnDefSingleResult()
	{
		$classes = $this->getHtmlClassesString();

		$possibleValues = $this->getPossibleEnumValues();

		return "

		" . $this->substituteUrlParameter() . "

		let selected = '';

		if(item)
			selected = '<option selected value=\"' + item[1] + '\">' + item[1] + '</option>';

		item = '<select data-populated=\"false\" " . $this->getValueString() . " class=\"" . $classes . " uk-select ib-editor-select\" data-url=\"' + url + '\" data-field=\"{$this->parameter}\">' + selected + '</select>';

		";
	}
}