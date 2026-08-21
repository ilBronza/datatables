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

	public bool $requiresRowSelectCheckbox = true;

	public function isBulkEditable() : bool
	{
		return true;
	}

	public $width = '125px';
	public $fieldType = 'text';

	//transformValue ritorna [key, value, label]
	public null|int|string $labelPosition = 2;

	public $nullValue = 'null';
	public $nullString;
	public $default = "null";
	public bool $associative = false;
	public ?array $possibleValuesArray = null;
	public bool $customValueMode = false;

	//rende il select una tendina select2 con ricerca testuale, inizializzata al primo click
	public bool $select2 = false;

	public ? bool $forceAlphabeticalSorting = false;

	public ? string $possibleValuesMethod = null;

	public ? string $possibleValuesRoute = null;

    public function __construct(string $name, array $parameters = [], int $index = null, DatatableField $parent = null, Datatables $table = null)
	{
		$this->nullString = config('datatables.labels.nd', 'nd');
		parent::__construct($name, $parameters, $index, $parent, $table);
	}

	public function isAssociative()
	{
		return $this->associative;
	}

	public function hasCustomValueMode() : bool
	{
		return $this->customValueMode;
	}

	public function hasSelect2() : bool
	{
		return $this->select2;
	}

	//classe aggiunta solo al select in cella, non all'input di selectOrInput ne' all'inline edit
	protected function getSelect2ClassString() : string
	{
		if(! $this->hasSelect2())
			return '';

		return ' ib-editor-select2';
	}

	public function getFieldSpecificData() : array
	{
		return array_merge(parent::getFieldSpecificData(), [
			'custom-value-mode' => $this->hasCustomValueMode(),
		]);
	}

	protected function getCustomValueModeDataAttribute() : string
	{
		if (! $this->hasCustomValueMode())
			return '';

		return ' data-custom-value-mode="true"';
	}

	protected function hasPossibleValuesRowRoute() : bool
	{
		return false;
	}

	protected function getPossibleValuesRowRouteDataAttributes() : string
	{
		return '';
	}

	protected function getPossibleValuesRowIdDataAttribute() : string
	{
		return '';
	}

	protected function getPossibleValuesRowRouteInlineDataAttributes() : array
	{
		return [];
	}

	protected function getInitialSelectLabel($selectedValue) : string
	{
		return $this->getSelectOptionLabel(
			$this->getPossibleEnumValuesArray(),
			$selectedValue
		);
	}

    public function parseFieldSpecificHeaderData()
    {
		if ($this->hasPossibleValuesRowRoute())
			return;

		$list = $this->getPossibleEnumValuesArray();

		if($this->isNullable())
			$list = array_merge([$this->nullValue => $this->nullString], $list);

		$this->setHeaderDataAttribute(
			'possibleValuesLegacy',
			json_encode($list)
		);
    }

    public function getPossibleEnumValuesArray()
    {
		if($method = $this->getPossibleValuesMethod())
			if($element = $this->element ?? $this->getPlaceholderElement())
				return $element->{$method}();

		if(isset($this->possibleValuesArray))
			return $this->possibleValuesArray;

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

		    $formrow = Formrow::gpc()::findCachedByField('slug', $formDataPieces[1]);

			$result = $formrow->getRowType()->getPossibleValuesArray();

		    if($this->forceAlphabeticalSorting)
		    	ksort($result);

		    return $result;

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
		$selectedValue = $value->{$propertyName} ?? $this->default;

		$selected = $this->getInitialSelectLabel($selectedValue);

		return [
			$this->element->getKey(),
			$selectedValue,
			$selected
		];
	}

	protected function getSelectOptionLabel(array $possibleValues, $key) : string
	{
		if ($key === null || $key === $this->nullValue)
			return $possibleValues['null'] ?? $this->nullString;

		if (isset($possibleValues['groups']) && is_array($possibleValues['groups']))
		{
			foreach ($possibleValues['groups'] as $options)
			{
				if (isset($options[$key]))
					return $options[$key];
			}

			return $this->nullString;
		}

		return $possibleValues[$key] ?? $this->nullString;
	}

	public function getInlineEditHtml() : string
	{
		if (! $this->userCanEdit())
			return '';

		$dataAttributes = array_merge(
			$this->getDataAttributes(),
			$this->getPossibleValuesRowRouteInlineDataAttributes()
		);
		$dataAttributes['url'] = $this->getInlineEditUpdateUrl();
		$dataAttributes['inline-source-field'] = $this->name;

		if (! $this->hasCustomValueMode())
			unset($dataAttributes['custom-value-mode']);

		$attributes = [];

		foreach ($dataAttributes as $name => $value)
			$attributes[] = 'data-' . e($name) . '="' . e($value) . '"';

		$value = $this->getInlineEditValue();
		$options = $this->hasPossibleValuesRowRoute()
			? []
			: $this->getPossibleEnumValuesArray();

		if ($this->isNullable())
			$options = array_merge([$this->nullValue => $this->nullString], $options);

		$optionsHtml = '';

		foreach ($options as $optionValue => $label)
		{
			$selected = (string) $optionValue === (string) $value ? ' selected' : '';
			$optionsHtml .= '<option value="' . e($optionValue) . '"' . $selected . '>' . e($label) . '</option>';
		}

		$classes = e(trim($this->getHtmlClassesString() . ' uk-select ib-editor-text ib-editor-select ib-datatable-inline-edit-field'));

		return '<select ' . implode(' ', $attributes) . ' data-originalvalue="' . e($value) . '" class="' . $classes . '">' . $optionsHtml . '</select>';
	}

	public function getCustomColumnDefSingleResult()
	{
		if(! $this->userCanEdit())
			return $this->returnFlat();

		$classes = $this->getHtmlClassesString() . $this->getSelect2ClassString();

		return "

		" . $this->substituteUrlParameter() . "

		let selected = '';

		if(item)
			selected = '<option selected value=\"' + item[1] + '\">' + item[2] + '</option>';

		item = '<select data-populated=\"false\"" . $this->getCustomValueModeDataAttribute() . $this->getPossibleValuesRowRouteDataAttributes() . $this->getPossibleValuesRowIdDataAttribute() . " " . $this->getValueString() . " class=\"" . $classes . " uk-select ib-editor-select\" data-url=\"' + url + '\" data-field=\"{$this->parameter}\">' + selected + '</select>';

		";
	}
}
