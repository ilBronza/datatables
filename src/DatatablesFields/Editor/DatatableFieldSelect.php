<?php

namespace IlBronza\Datatables\DatatablesFields\Editor;

use DB;
use IlBronza\Datatables\Datatables;
use IlBronza\Datatables\DatatablesFields\FieldTypesTraits\EditorSingleFieldTrait;

use IlBronza\FileCabinet\Helpers\DossierCreatorHelper;
use IlBronza\FileCabinet\Helpers\DossierrowFormFieldHelper;

use IlBronza\FileCabinet\Models\Form;

use IlBronza\FileCabinet\Models\Formrow;

use function dd;
use function explode;
use function preg_match_all;

class DatatableFieldSelect extends DatatableFieldEditor
{
	use EditorSingleFieldTrait;

	public $width = '125px';
	public $fieldType = 'text';

	public $nullValue = 'null';
	public $nullString = 'nd';

	public ? string $possibleValuesMethod = null;

    public function __construct(string $name, array $parameters = [], int $index = null, DatatableField $parent = null, Datatables $table = null)
	{
		parent::__construct($name, $parameters, $index, $parent, $table);
	}

    public function parseFieldSpecificHeaderData()
    {
		$list = $this->getPossibleEnumValuesArray();

		if($this->isNullable())
			$list = array_merge([$this->nullValue => $this->nullString], $list);

		$this->setHeaderDataAttribute(
			'possibleValues',
			json_encode($list)
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

	    if($method = $this->getPossibleValuesMethod())
			return $element->$method();

		if(isset($this->possibleValuesArray))
			return $this->possibleValuesArray;

	    if($this->isDossierrow())
	    {
			$cast = $this->getCast();

			$pieces = explode(':', $cast);

			$formDataPieces = explode(",", $pieces[1]);

//		    $form = Form::gpc()::findCachedByField('slug', $formDataPieces[0]);
		    $formrow = Formrow::gpc()::findCachedByField('slug', $formDataPieces[1]);

//		    $dossierrow = DossierCreatorHelper::getOrFakeDossierrowByTargetFormFormrow($element, $form, $formrow);

			return $formrow->getRowType()->getPossibleValuesArray();
	    }

		return cache()->remember('getPossibleEnumValues' . $element->getTable() . 'field' . $this->name, 3600, function() use ($element)
		{
			// $_enumStr = \DB::select(\DB::raw('SHOW COLUMNS FROM ' . $element->getTable() . ' WHERE Field = "' . $this->name . '"'));

			$expression = DB::raw('SHOW COLUMNS FROM ' . $element->getTable() . ' WHERE Field = "' . $this->name . '"');
			$string = $expression->getValue(DB::connection()->getQueryGrammar());


			$_enumStr = DB::select($string);

			if(! isset($_enumStr[0]))
				return [];

			$enumStr = $_enumStr[0]->Type;
			preg_match_all("/'([^']+)'/", $enumStr, $matches);

			return $matches[1] ?? [];
		});
    }

	public function transformValue($value)
	{
		if ($this->hasForceValue())
		{
			if (! $this->requireElement())
				return $value;

			return [
				$value->getKey(),
				$this->forceValue
			];
		}

		if (isset($this->solveElement))
			$value = $this->getFieldCellDataValue($this->name, $value);

		if (! $this->requireElement())
			return $value;

		$this->element = $value;

		if ($this->editorValueFunction)
			return [
				$this->element->getKey(),
				$this->element->{$this->editorValueFunction}()
			];

		$propertyName = $this->editorProperty ?? $this->name;

		$selected = $this->getPossibleEnumValuesArray()[$value->{$propertyName}] ?? $this->nullString;

		return [
			$this->element->getKey(),
			$value->{$propertyName} ?? $this->nullValue,
			$selected
		];
	}

	public function getCustomColumnDefSingleResult()
	{
		if(! $this->userCanEdit())
			return $this->returnFlat();

		$classes = $this->getHtmlClassesString();

		return "

		" . $this->substituteUrlParameter() . "

		let selected = '';

		if(item)
			selected = '<option selected value=\"' + item[1] + '\">' + item[2] + '</option>';

		item = '<select data-populated=\"false\" " . $this->getValueString() . " class=\"" . $classes . " uk-select ib-editor-select\" data-url=\"' + url + '\" data-field=\"{$this->parameter}\">' + selected + '</select>';

		";
	}
}