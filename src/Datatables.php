<?php

namespace IlBronza\Datatables;

use App\Models\Appointment;
use Closure;
use IlBronza\Datatables\DatatablesFields\DatatableField;
use IlBronza\Datatables\DatatablesFields\DatatableFieldPrimary;
use IlBronza\Datatables\DatatablesFields\DatatableFieldSelectRowCheckbox;
use IlBronza\Datatables\DatatablesFields\Editor\DatatableFieldEditor;
use IlBronza\Datatables\DatatablesFields\Editor\DatatableFieldFileUpload;
use IlBronza\Datatables\DatatablesFields\Links\DatatableFieldLink;
use IlBronza\Datatables\Traits\DatatableButtonsTrait;
use IlBronza\Datatables\Traits\DatatableColumnDefsTrait;
use IlBronza\Datatables\Traits\DatatableColumnDisplayTrait;
use IlBronza\Datatables\Traits\DatatableDataTrait;
use IlBronza\Datatables\Traits\DatatableDomTrait;
use IlBronza\Datatables\Traits\DatatableFieldsTrait;
use IlBronza\Datatables\Traits\DatatableFiltersTrait;
use IlBronza\Datatables\Traits\DatatableFixedColumnsTrait;
use IlBronza\Datatables\Traits\DatatableFormTrait;
use IlBronza\Datatables\Traits\DatatableModelBroadcastsTrait;
use IlBronza\Datatables\Traits\DatatableOptionsTrait;
use IlBronza\Datatables\Traits\DatatableSaveStateTrait;
use IlBronza\Datatables\Traits\DatatableSelectRowsTrait;
use IlBronza\Datatables\Traits\DatatablesExtraViewsTrait;
use IlBronza\Form\Form;
use IlBronza\Form\Traits\ExtraViewsTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

use function implode;

class Datatables
{
	use DatatableDomTrait;
	use DatatableFormTrait;
	use DatatableButtonsTrait;
	use DatatableDataTrait;
	use DatatableFieldsTrait;
	use DatatableColumnDefsTrait;
	use DatatableSaveStateTrait;
	use DatatablesExtraViewsTrait;
	use ExtraViewsTrait;
	use DatatableOptionsTrait;
	use DatatableModelBroadcastsTrait;
	use DatatableSelectRowsTrait;
	use DatatableColumnDisplayTrait;
	use DatatableFiltersTrait;
	use DatatableFixedColumnsTrait;

	static $availableExtraViewsPositions = [
		'top',
		'bottom',
		'left',
		'right'
	];

	//DatatableSaveStateTrait
	public ? bool $saveState = null;

	//DatatableFiltersTrait
	public ? bool $removeFiltersButton = null;
	public ?bool $filtersHidden = null;

	public int $fixedColumnsLeft = 0;
	public int $fixedColumnsRight = 0;

	public $caption;
	public $columnDisplayKey;
	public $rowId;
	public $fields;
	public $summary;
	public ?string $name;
	public $fieldsGroups;
	public $elements;
	public $elementsProvider;
	public $url;
	public $columnDefs = [];

	public $columnOptions;
	public $createdRowScripts = [];
	public $buttons;

	public $extraViews = null;

	public ?string $cachedTableKey;

	public ?array $data;

	public ?Form $form = null;
	public ?bool $mustPrintIntestation = null;

	public array $htmlClasses = [];

	public ? bool $footerFilters;

	public $getRowIdIndex = false;
	public $stripe = true;
	public $pageLength = null;
	public $options = [];
	public bool $hasSorting = false;
	public $sourceType = 'ajax';
	public $variables = [];
	public $modelClass;
	public $dom;
	public $canHideColumns;
	public ?bool $canUseFieldsGroups = null;
	public ?bool $canEditColumnStyles = null;
	public $customButtons;
	public $selectRowCheckboxes;
	public ?bool $bulkEdit = null;
	public ?bool $bulkEditOnSelection = null;
	public $placeholderElement;
	public $datatableUserData;
	public ?bool $copyButton = null;
	public ?bool $csvButton = null;
	public ?bool $excelButton = null;
	public bool $hasInlineCreate = false;

	public $scrollX = true;
	public ? bool $scrollY = null;

	public $domStickyButtons;
	public $domStickyHeader;

	public $filterOnEnter = false;

	public string $ajaxMethod = 'GET';


	public function __construct()
	{
		$this->fields = collect();
		$this->fieldsGroups = collect();

		$this->customButtons = collect();

		$this->initializeButtons();

		$this->setFetchers();
	}

	public function getFetchersFromController(object $controller) : static
	{
		if (! method_exists($controller, 'getFetchers'))
			throw new \InvalidArgumentException('The controller must expose a getFetchers method.');

		return $this->setFetchers($controller->getFetchers());
	}

	public function getAjaxMethod() : string
	{
		return $this->ajaxMethod;
	}

	public function setAjaxMethod(string $ajaxMethod)
	{
		$this->ajaxMethod = $ajaxMethod;
	}

	static function createStandAloneTable(array $parameters)
	{
		$name = $parameters['name'];
		$fieldsGroups = $parameters['fieldsGroups'] ?? 'index';
		$elements = $parameters['elements'];
		$elementsProvider = is_callable($elements) ? $elements : function () use ($elements)
		{
			return $elements;
		};
		$selectRowCheckboxes = $parameters['selectRowCheckboxes'] ?? false;
		$extraVariables = $parameters['extraVariables'] ?? null;
		$modelClass = $parameters['modelClass'] ?? null;
		$bulkEdit = $parameters['bulkEdit'] ?? null;
		$bulkEditOnSelection = $parameters['bulkEditOnSelection'] ?? null;

		$table = static::create(
			$name, $fieldsGroups, $elementsProvider, $selectRowCheckboxes, $extraVariables, $modelClass, $bulkEdit, $bulkEditOnSelection
		);

		if ((request()->ajax()) && (request()->model))
			return $table;

		$table->setArrayTable();

		unset($parameters['fieldsGroups']);
		unset($parameters['elements']);

		$table->bind($parameters);

		return $table;
	}

	static function create(string $name, array $fieldsGroups, $elements, bool $selectRowCheckboxes = false, array $extraVariables = null, string $modelClass = null, ?bool $bulkEditFromController = null, ?bool $bulkEditOnSelectionFromController = null)
	{
		ini_set('memory_limit', '512M');

		$table = new static();

		$table->setMainModelElement($modelClass);

		$table->cleanCachedTableKeyParameterIfEditor();

		if (! is_null($bulkEditFromController))
			$table->setBulkEditFromController($bulkEditFromController);

		if (! is_null($bulkEditOnSelectionFromController))
			$table->setBulkEditOnSelectionFromController($bulkEditOnSelectionFromController);

		if (($selectRowCheckboxes) || $table->fieldsGroupsRequiresSelectRowCheckboxes($fieldsGroups))
			$table->setRowSelectCheckboxes();

		$table->setVariables($extraVariables ?? []);
		$table->setElementsProvider($elements);

		if ((request()->ajax()) && (! request()->ibFetcher))
		{
			// if (($table->cachedTableKey = request()->input('cachedtablekey')) && ($data = cache()->pull($table->cachedTableKey)))
			// {
			// 	$table->setData($data, $selectRowCheckboxes);
			//
			// 	return $table;
			// }

			$table->addFieldsGroups($fieldsGroups);
			// $table->addFields($fields);

			if (request()->has('rowId'))
				return $table->returnSingleElement($elements);

			if (request()->has('rowIds'))
			{
				return $table->returnSelectedElements($elements);
			}

			$elements = $elements();
			$table->setElements($elements);
			$table->setData();

			return $table;
		}
		// return "HTTP";

		$table->addFieldsGroups($fieldsGroups);

		$table->setName($name);
		$table->setUrl(request()->url());

		// $table->setElements($elements());

		// $table->setAutomaticCaption();

		return $table;
	}

	public static function createInlineEditTable(string $name, array $fieldsGroup, Model $element, ?array $sourceFieldsGroup = null) : static
	{
		if ($sourceFieldsGroup)
			$fieldsGroup = static::makeInlineEditFieldsGroup($name, $fieldsGroup, $sourceFieldsGroup, $element);

		$table = new static();

		$table->setMainModelElement($element::class);
		$table->setElements(collect([$element]));
		$table->addFieldsGroups([$name => $fieldsGroup]);

		foreach ($table->getFields() as $field)
		{
			if ($field instanceof DatatableFieldEditor)
				$field->setInlineEditElement($element);
		}

		return $table;
	}

	public static function createInlineCreateTable(string $name, array $fieldsGroup, Model $element, ?array $sourceFieldsGroup = null) : static
	{
		$table = static::createInlineEditTable(
			$name,
			$fieldsGroup,
			$element,
			$sourceFieldsGroup
		);
		$storeUrl = $table->getInlineCreateStoreUrl();

		foreach ($table->getInlineEditFields() as $field)
			$field->setInlineCreateElement($element, $storeUrl);

		return $table;
	}

	/**
	 * Build inline edit/create fields from the source table.
	 *
	 * Inline fields override source fields of the same name. Every remaining
	 * data column uses the source field's inline type or the text editor, while
	 * action, identity and file controls stay out of the batch editor.
	 *
	 * Con onlyDeclaredFields a true nel gruppo inline i campi sorgente non
	 * dichiarati non vengono derivati: restano solo quelli del file inline,
	 * ordinati come nella tabella sorgente.
	 */
	public static function makeInlineEditFieldsGroup(string $name, array $inlineFieldsGroup, array $sourceFieldsGroup, Model $element) : array
	{
		$inlineFields = $inlineFieldsGroup['fields'] ?? [];
		$onlyDeclaredFields = $inlineFieldsGroup['onlyDeclaredFields'] ?? false;
		$sourceTable = new static();

		$sourceTable->setMainModelElement($element::class);
		$sourceTable->setElements(collect([$element]));
		$sourceTable->addFieldsGroups([$name => $sourceFieldsGroup]);

		$fields = [];
		$usedInlineFields = [];

		foreach ($sourceTable->getFields() as $sourceField)
		{
			$sourceFieldName = $sourceField->getFieldName();
			$inlineFieldName = static::findInlineEditFieldForSourceField($inlineFields, $sourceFieldName);

			if ($inlineFieldName)
			{
				$fields[$inlineFieldName] = $inlineFields[$inlineFieldName];
				$usedInlineFields[$inlineFieldName] = true;

				continue;
			}

			if ($onlyDeclaredFields)
				continue;

			if (! static::canCreateInlineField($sourceField))
				continue;

			$fields[$sourceFieldName] = [
				'type' => $sourceField->getInlineFieldType(),
				'inlineEditProperty' => $sourceFieldName,
				'translatedName' => $sourceField->getTranslatedName(),
			];
		}

		foreach ($inlineFields as $inlineFieldName => $inlineField)
			if (! isset($usedInlineFields[$inlineFieldName]))
				$fields[$inlineFieldName] = $inlineField;

		return [
			'translationPrefix' => $inlineFieldsGroup['translationPrefix'] ?? $sourceFieldsGroup['translationPrefix'] ?? null,
			'fields' => $fields,
		];
	}

	protected static function findInlineEditFieldForSourceField(array $inlineFields, string $sourceFieldName) : ?string
	{
		return array_key_exists($sourceFieldName, $inlineFields)
			? $sourceFieldName
			: null;
	}

	protected static function canCreateInlineField(DatatableField $field) : bool
	{
		return (! $field instanceof DatatableFieldPrimary)
			&& (! $field instanceof DatatableFieldSelectRowCheckbox)
			&& (! $field instanceof DatatableFieldLink)
			&& (! $field instanceof DatatableFieldFileUpload);
	}

	public function getInlineEditFields() : Collection
	{
		return $this->getFields()->filter(
			fn ($field) => $field instanceof DatatableFieldEditor
		);
	}

	public function hasRangeFilter() : bool
	{
		foreach ($this->getFields() as $field)
			if ($field->hasRangeFilter())
				return true;

		return false;
	}

	public function setVariables(array $extraVariables)
	{
		foreach ($extraVariables as $name => $value)
			$this->setVariable($name, $value);
	}

	public function setVariable(string $name, $value)
	{
		$this->variables[$name] = $value;
	}

	public function returnSelectedElements(callable $elements)
	{
		if ($elements instanceof Closure)
		{
			if (! $elements = $elements())
				mori('nessun elemento');

			if (! $firstElement = $elements->first())
				mori('nessun elemento');

			$_elements = $elements->whereIn(
				$firstElement->getKeyName(), request()->rowIds
			);

			$this->setElements($_elements);
			$this->setData();

			return $this;
		}

		mori('non instanceof Closure, vuol dire che è una query o una collection, zio culo culo culo culo cazzo culo cazzo culo merda');
	}

	public function returnSingleElement(callable $elements)
	{
		if ($elements instanceof Closure)
		{
			if (! $elements = $elements())
				mori('nessun elemento');

			if (! $firstElement = $elements->first())
				mori('nessun elemento');

			$element = $elements->firstWhere(
				$firstElement->getKeyName(), request()->input('rowId')
			);

			$collection = collect();

			if ($element)
				$collection->push($element);

			$this->setElements($collection);
			$this->setData();

			return $this;
		}

		mori('non instanceof Closure, vuol dire che è una query o una collection, zio culo culo culo culo cazzo culo cazzo culo merda');
	}

	public function setElements(Collection $elements = null)
	{
		$this->elements = $elements;
	}

	public function setElementsProvider(callable $elementsProvider)
	{
		$this->elementsProvider = $elementsProvider;
	}

	public function resolveElements()
	{
		if ($this->elements)
			return;

		if (! $this->elementsProvider)
			return;

		$this->setElements(($this->elementsProvider)());
	}

	public function setName(string $name)
	{
		$this->name = $name;

		$this->setCachedTableKey();
	}

	public function setArrayTable()
	{
		$this->sourceType = 'array';
	}

	public function bind(array $parameters)
	{
		foreach ($parameters as $key => $value)
			$this->{$key} = $value;
	}

	public function hasMainHeader() : bool
	{
		foreach ($this->getFields() as $field)
			if ($field->hasMainHeader())
				return true;

		return false;
	}

	public function addHtmlClass(string $classname) : self
	{
		$this->htmlClasses[] = $classname;

		return $this;
	}

	public function getHtmlClassesString() : ?string
	{
		return implode(" ", $this->getHtmlClasses());
	}

	public function getHtmlClasses() : array
	{
		return $this->htmlClasses;
	}

	public function getValidExtraViewsPositions() : array
	{
		return static::$availableExtraViewsPositions;
	}

	public function setModelClass(?string $modelClass) : static
	{
		$this->modelClass = $modelClass;

		return $this;
	}

	public function addBaseModelClass(string $modelClass) : static
	{
		return $this->setModelClass($modelClass);
	}

	public function getVariable(string $name)
	{
		return $this->variables[$name] ?? null;
	}

	public function getUrl()
	{
		return $this->url;
	}

	public function setUrl(string $url)
	{
		$this->url = $url;
	}

	public function getCamelName()
	{
		return Str::camel($this->getName());
	}

	public function getName() : ?string
	{
		if ($this->name ?? false)
			return $this->name;

		$this->setName('table' . rand(0, 99999));

		return $this->getName();
	}

	public function getStripeClass()
	{
		if ($this->stripe)
			return 'stripe row-border uk-table-striped';
	}

	public function getNextFieldIndex()
	{
		$index = $this->fields->max('index') ?? 0;

		// $index = -1;

		// foreach($this->fields as $field)
		//     if($field->index > $index)
		//         $index = $field->index;

		return $index + 1;
	}

	public function renderPage()
	{
		if (request()->ibFetcher)
			return $this->renderPortion();

		if ($this->isFlatTable())
			return view('datatables::table', [
				'table' => $this,
				'tableSourceData' => $this->prepareCachedData()
				// 'tableSourceData' => $this->calculateData()
			]);

		if ((request()->ajax()) && (! request()->ibFetcher))
			return [
				"draw" => 1,
				"recordsTotal" => count($this->getData()),
				"recordsFiltered" => count($this->getData()),
				"data" => $this->getData()
			];

		if (! $this->isAjaxTable())
			$this->prepareCachedData();
		$this->parseColumnDefs();

		$this->parseOptions();

		return view('datatables::table', ['table' => $this]);
	}

	public function isFlatTable()
	{
		return $this->sourceType == 'flat';
	}

	public function renderPortion()
	{
		$this->parseColumnDefs();
		$this->parseOptions();

		$viewData = [
			'table' => $this,
		];

		if (! $this->isAjaxTable())
			$viewData['tableSourceData'] = $this->calculateData();

		return view('datatables::_table', $viewData)->render();
	}

	public function render()
	{
		$this->parseColumnDefs();

		return view('datatables::_table', ['table' => $this]);
	}

	public function renderInlineEdit() : string
	{
		return view('datatables::inlineEdit', ['table' => $this])->render();
	}

	public function renderInlineCreate() : string
	{
		return view('datatables::inlineCreate', ['table' => $this])->render();
	}

	public function getId()
	{
		return $this->id ?? $this->cachedTableKey ?? ($this->id = 'table_fake_id' . rand(0, 99999999));
	}

	public function setFlatTable()
	{
		$this->sourceType = 'flat';
	}

	public function setAjaxTable()
	{
		$this->sourceType = 'ajax';
	}

	public function isAjaxTable()
	{
		return $this->sourceType == 'ajax';
	}

	public function isArrayTable()
	{
		return $this->sourceType == 'array';
	}

	public function setScrollY(bool $scrollY)
	{
		$this->scrollY = $scrollY;
	}

	public function setScrollX(bool $scrollX)
	{
		$this->scrollX = $scrollX;
	}

	public function canScrollX()
	{
		if(! is_null($this->scrollX))
			return $this->scrollX;

		return config('datatables.scrollX');
	}

	public function canScrollY()
	{
		if(! is_null($this->scrollY))
			return $this->scrollY;

		return config('datatables.scrollY');		
	}

	//TODO PROTEGGERE QUESTA CON DEI FILLABLE???

	public function getRelationName()
	{
		return $this->getName();
	}

	public function handleError($e)
	{
		if ($this->debug())
			throw $e;

		return $e->getMessage();
	}

	public function debug() : bool
	{
		return config('datatables.debug', false);
	}

	private function cleanCachedTableKeyParameterIfEditor()
	{
		if (request()->ajax())
			if (request()->input('ibeditor'))
				request()->request->remove('cachedtablekey');
	}
}
