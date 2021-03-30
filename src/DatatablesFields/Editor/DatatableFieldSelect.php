<?php

namespace IlBronza\Datatables\DatatablesFields\Editor;

use IlBronza\Datatables\DatatablesFields\FieldTypesTraits\EditorSingleFieldTrait;

class DatatableFieldSelect extends DatatableFieldEditor
{
	use EditorSingleFieldTrait;

	public $width = '125px';
	public $fieldType = 'text';

    public function __construct(string $name, array $parameters = [], int $index = null)
	{
		parent::__construct($name, $parameters, $index);
	}

    public function parseFieldSpecificHeaderData()
    {
		$this->setHeaderData(
			'possibleValues',
			json_encode($this->getPossibleEnumValuesArray())
		);        
    }

    public function getPossibleEnumValuesArray()
    {
        $values = $this->getPossibleEnumValues();

        $result = [];

        foreach($values as $value)
            $result[$value] = $value;

        return $result;
    }

	public function getPossibleEnumValues()
    {
        $_enumStr = \DB::select(\DB::raw('SHOW COLUMNS FROM ' . $this->element->getTable() . ' WHERE Field = "' . $this->name . '"'));

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