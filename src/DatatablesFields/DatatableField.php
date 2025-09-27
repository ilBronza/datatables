<?php

namespace IlBronza\Datatables\DatatablesFields;

use Exception;
use IlBronza\Datatables\DatatableFieldsGroup;
use IlBronza\Datatables\Datatables;
use IlBronza\Datatables\DatatablesFields\Editor\DatatableFieldEditor;
use IlBronza\Datatables\DatatablesFields\FieldTypesTraits\CssTrait;
use IlBronza\Datatables\DatatablesFields\FieldTypesTraits\DataAttributesTrait;
use IlBronza\Datatables\DatatablesFields\FieldTypesTraits\HtmlClassesAttributesTrait;
use IlBronza\Datatables\DatatablesFields\FieldTypesTraits\TextAlignTrait;
use IlBronza\Datatables\Traits\DatatablesFields\DatatablesFieldsColumnDefsTrait;
use IlBronza\Datatables\Traits\DatatablesFields\DatatablesFieldsDisplayTrait;
use IlBronza\Datatables\Traits\DatatablesFields\DatatablesFieldsElementTrait;
use IlBronza\Datatables\Traits\DatatablesFields\DatatablesFieldsFetcherTrait;
use IlBronza\Datatables\Traits\DatatablesFields\DatatablesFieldsFiltersTrait;
use IlBronza\Datatables\Traits\DatatablesFields\DatatablesFieldsFormTrait;
use IlBronza\Datatables\Traits\DatatablesFields\DatatablesFieldsHtmlClassesTrait;
use IlBronza\Datatables\Traits\DatatablesFields\DatatablesFieldsIdentifiersTrait;
use IlBronza\Datatables\Traits\DatatablesFields\DatatablesFieldsOperationsTrait;
use IlBronza\Datatables\Traits\DatatablesFields\DatatablesFieldsParametersTrait;
use IlBronza\Datatables\Traits\DatatablesFields\DatatablesFieldsParentingTrait;
use IlBronza\Datatables\Traits\DatatablesFields\DatatablesFieldsPermissionsTrait;
use IlBronza\Datatables\Traits\DatatablesFields\DatatablesFieldsSortingTrait;
use IlBronza\Datatables\Traits\DatatablesFields\DatatablesFieldsStructuredDataIndexTrait;
use IlBronza\Datatables\Traits\DatatablesFields\DatatablesFieldsSummaryTrait;
use IlBronza\Datatables\Traits\DatatablesFields\DatatablesFieldsUserDataTrait;

class DatatableField
{
	use DatatablesFieldsStructuredDataIndexTrait;
	use DatatablesFieldsPermissionsTrait;
	use DatatablesFieldsIdentifiersTrait;
	use DatatablesFieldsDisplayTrait;
	use DatatablesFieldsColumnDefsTrait;
	use DatatablesFieldsSummaryTrait;
	use DatatablesFieldsParametersTrait;
	use DatatablesFieldsFiltersTrait;
	use DatatablesFieldsSortingTrait;
	use DatatablesFieldsHtmlClassesTrait;
	use DatatablesFieldsOperationsTrait;
	use DatatablesFieldsFetcherTrait;
	use DatatablesFieldsElementTrait;
	use DatatablesFieldsParentingTrait;
	use DatatablesFieldsUserDataTrait;

	use DatatablesFieldsFormTrait;

	public $form = null;

	use TextAlignTrait;
	use CssTrait;

	use HtmlClassesAttributesTrait;
	use DataAttributesTrait;

	public $id;
	public $name;
	public ?string $forcedStandardName;
	public $index;
	public $avoidIcon;
	public $order;
	public $fetcher;
	public $visible;
	public $forceValue;

	public ?string $overridingValueMethod = null;
	public $rowId = false;
	public $tooltip = false;
	public $summary;
	public $data = [];
	public $mainHeader = null;
	public $property;
	public $headerData = [];
	public $footerData = [];
	public $columnDefs = [];
	public $fieldSpecificClasses = [];
	public $customColumnDefs = [];
	public $columnOptions = [];
	public $icon;
	public $showLabel = true;
	public $htmlClasses = [];
	public $tDHtmlClasses = [];

	public ?bool $mustPrintIntestation = null;

	public $headerHtmlClasses = [];
	public $footerHtmlClasses = [];
	public $fieldOperations = [];
	public $fieldExtraData = [];
	public $filteredTable;
	public $parameter;

	public $roles = null;

	public ?string $translatedName = null;
	public ?string $suffix = null;

	public bool $truncateText = false;
	public $filterType;
	public $type;
	public $table;
	public $userData;
	public $rangeFilter;
	public $summaryValues;
	public $elementValues;
	public $function;
	public $sortable = true;
	public $requireElement = false;
	public $requiresPlaceholderElement = false;
	public $placeholderElement;
	public $element;
	public $valueAsRowClass = false;
	public $valueAsRowClassPrefix = false;
	public $compiledAsRowClass;
	public $compiledAsRowClassPrefix;
	public $extraModelClassname;
	public $htmlTag;
	public $width;
	public $target;

	public $modelClass;
	public ?string $renderAs;
	public $doubler = false;
	public $jqueryFilterEvents = ['change', 'keyup'];

	public $strLimit = 0;

	public ?string $translationPrefix = null;

	public DatatableFieldsGroup $fieldsGroup;

	public $canDrawTable = true;

	//nuovo campo per capire se è abilitato il filtro o meno
	public $filterable = true;
	public $defaultFilterType = 'text';
	public $availableColumnOptions = ['order'];
	public $availableColumnDefs = [
		'width' => 'width',
		'orderDataType' => 'orderDataType',
		'visible' => 'visible',
		'datatableType' => 'type'
	];

	public function __construct(string $name, array $parameters = [], int $index = null, DatatableField $parent = null, Datatables $table = null)
	{
		$this->name = $name;

		$this->id = $this->generateId();

		$this->table = $table;

		$this->setParentField($parent);

		$this->setParameters($parameters);

		$this->setPlaceholderElement();

		$this->manageWidth($parameters);

		$this->setIndex($index);

		// $this->setType();
		$this->setColumnDefs();
		$this->setColumnOptions();

		$this->setHtmlClasses($parameters);
		$this->setDataAttributes($parameters);

		$this->manageFieldOperations($parameters);

		$this->setFetcherParameters($parameters);

		$this->summaryValues = collect();

		$this->checkConfirmMessage($parameters);
	}

	//this is used to tell datatables how to render sorting value
	// public $datatableType = 'string';

	static function getPackageClassNameByType(string $fieldType) : string
	{
		$pieces = explode("::", $fieldType);

		$package = array_shift($pieces);

		$fieldType = static::getClassNameByType(implode(".", $pieces), false);

		if ($package == 'app')
			$fullnamespace = "App\\";
		else
			$fullnamespace = get_class(app($package));

		$namespacePieces = explode("\\", $fullnamespace);
		array_pop($namespacePieces);

		return implode("\\", $namespacePieces) . "\Providers\DatatablesFields\\" . $fieldType;
	}

	static function getClassNameByType(string $fieldType, bool $fullQualifiedNamespace = true) : string
	{
		//if contains "::" in string, it's a custom field
		if (strpos($fieldType, "::") !== false)
			return static::getPackageClassNameByType($fieldType);

		$pieces = explode(".", $fieldType);

		$fieldType = ucfirst(array_pop($pieces));

		$folders = implode("", array_map(function ($item)
		{
			return ucfirst($item) . '\\';
		}, $pieces));

		if (! $fullQualifiedNamespace)
			return $folders . "DatatableField" . $fieldType;

		return __NAMESPACE__ . '\\' . $folders . "DatatableField" . $fieldType;
	}

	static function createByType(string $fieldName, string $fieldType, array $fieldParameters, int $index = 0, DatatableField $parent = null, Datatables $table = null)
	{
		$className = static::getClassNameByType($fieldType);

		return new $className($fieldName, $fieldParameters, $index, $parent, $table);
	}

	public function isEditor() : bool
	{
		return $this instanceof DatatableFieldEditor;
	}

	public function addExtradata(string $key, mixed $value)
	{
		$this->fieldExtraData[$key] = $value;
	}

	public function hasDoubler()
	{
		return $this->doubler;
	}

	public function getFieldCellDataValue(string $fieldName, $element)
	{
		$properties = explode(".", $fieldName);

		$this->elementValues = [];

		$i = 0;

		$this->elementValues[$i] = $element;

		do
		{
			$property = array_shift($properties);

			if (strpos($property, 'mySelf') === false)
				$element = isset($element->$property) ? $element->$property : null;

			$this->elementValues[++ $i] = $element;
		} while (count($properties));

		return $element;
	}

	public function hasOverridingValueMethod() : bool
	{
		return ! ! $this->overridingValueMethod;
	}

	public function getOverridingValueMethod() : ?string
	{
		return $this->overridingValueMethod;
	}

	public function getType()
	{
		return $this->type;
	}

	public function isFlatType()
	{
		return $this->getType() == 'flat';
	}

	public function requiresKey()
	{
		if ($this->getFetcherData())
		{
			if ($this->isFlatType())
				return false;

			return true;
		}

		return false;
	}

	public function __toString()
	{
		unset($this->table);

		return json_encode($this);
	}

	public function getPropertyName()
	{
		return $this->property ?? false;
	}

	public function transformValue($value)
	{
		return $value;
	}

	public function requireElement()
	{
		return $this->requireElement;
	}

	public function isRowId()
	{
		return $this->rowId;
	}

	public function getRenderAsType()
	{
		if (isset($this->renderAs))
			return $this->renderAs;

		return;

		try
		{
			return $this->type;
		}
		catch (Exception $e)
		{
			dd('render as missing ' . $this->name);
		}
	}

	public function setFieldsGroup(DatatableFieldsGroup $fieldsGroup)
	{
		$this->fieldsGroup = $fieldsGroup;
	}

	public function getFieldsGroup() : DatatableFieldsGroup
	{
		return $this->fieldsGroup;
	}

	/**
	 * add columnDef to field
	 *
	 * @param  string  $name
	 * @param  mixed   $value
	 */
	public function addColumnOption(string $name, $value)
	{
		$this->columnOptions[$name] = $value;
	}

	public function getColumnOptions()
	{
		return $this->columnOptions;
	}

	public function handleError($e)
	{
		return $e->getMessage();
	}

	public function getHtmlTagString()
	{
		return $this->getHtmlTag() . ' ';
	}

	public function getHtmlTag()
	{
		return $this->htmlTag;
	}

	public function getTable() : Datatables
	{
		return $this->table;
	}

	public function debug() : bool
	{
		return $this->getTable()->debug();
	}

	public function getElement()
	{
		return $this->element ?? $this->getPlaceholderElement();
	}

	public function getCast() : ?string
	{
		$element = $this->getElement();

		$casts = $element->getCasts();

		return $casts[$this->getName()] ?? null;
	}

	public function isDossierrow() : bool
	{
		$element = $this->getElement();

		$casts = $element->getCasts();

		if (! $cast = $this->getCast())
			return false;

		return stripos($cast, 'ExtraFieldDossier') !== false;
	}

	/**
	 * set field own options
	 */
	private function setColumnOptions()
	{
		//parse through available columnDefs parameters and id set, store it
		foreach ($this->availableColumnOptions as $availableColumnOption)
			$this->setColumnOption($availableColumnOption);
	}

	/**
	 * if exists in object, add columnOption to field
	 */
	private function setColumnOption(string $columnOption)
	{
		if (($value = $this->$columnOption ?? null) !== null)
			$this->addColumnOption($columnOption, $value);
	}

}
