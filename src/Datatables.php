<?php

namespace IlBronza\Datatables;

use App\Models\Appointment;
use IlBronza\Datatables\Traits\DatatableButtonsTrait;
use IlBronza\Datatables\Traits\DatatableColumnDefsTrait;
use IlBronza\Datatables\Traits\DatatableColumnDisplayTrait;
use IlBronza\Datatables\Traits\DatatableDataTrait;
use IlBronza\Datatables\Traits\DatatableDomTrait;
use IlBronza\Datatables\Traits\DatatableFieldsTrait;
use IlBronza\Datatables\Traits\DatatableFormTrait;
use IlBronza\Datatables\Traits\DatatableOptionsTrait;
use IlBronza\Datatables\Traits\DatatableSelectRowsTrait;
use IlBronza\Datatables\Traits\DatatablesExtraViewsTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class Datatables
{
    use DatatableDomTrait;
    use DatatableFormTrait;
    use DatatableButtonsTrait;
    use DatatableDataTrait;
    use DatatableFieldsTrait;
    use DatatableColumnDefsTrait;
    use DatatablesExtraViewsTrait;
    use DatatableOptionsTrait;
    use DatatableSelectRowsTrait;
    use DatatableColumnDisplayTrait;

    public $columnDisplayKey;
    public $rowId;
    public $fields;
    public $fieldsGroups;
    public $elements;
    public $url;
    public $columnDefs = [];
    public $createdRowScripts = [];
    public $buttons = [
        [
            'extend' => 'copy',
            'exportOptions' => ['orthogonal' => 'export']
        ],
        [
            'extend' => 'csv',
            'exportOptions' => ['orthogonal' => 'export']
        ]
    ];

    public $debug = false;
    public $getRowIdIndex = false;
    public $stripe = true;
    public $pageLength = 50;
    public $options = [];
    public $sourceType = 'ajax';
    public $variables = [];
    public $modelClass;
    public $dom;
    public $canHideColumns;
    public $customButtons;
    public $selectRowCheckboxes;
    public $placeholderElement;
    public $datatableUserData;

    public $scrollX = true;

    public $domStickyButtons;
    public $domStickyHeader;

    public $filterOnEnter = false;

    public function __construct()
    {
        $this->fields = collect();
        $this->fieldsGroups = collect();

        $this->customButtons = collect();
    }

    public function debug() : bool
    {
        return $this->debug;
    }

    public function addBaseModelClass(string $modelClass)
    {
        $this->modelClass = $modelClass;
    }

    public function setVariables(array $extraVariables)
    {
        foreach($extraVariables as $name => $value)
            $this->setVariable($name, $value);        
    }

    public function setVariable(string $name, $value)
    {
        $this->variables[$name] = $value;
    }

    public function getVariable(string $name)
    {
        return $this->variables[$name] ?? null;
    }

    private function cleanCachedTableKeyParameterIfEditor()
    {
        if(request()->ajax())
            if(request()->input('ibeditor'))
                request()->request->remove('cachedtablekey');
    }

    public function returnSingleElement(callable $elements)
    {
        if($elements instanceof \Closure)
        {
            if(! $elements = $elements())
                mori('nessun elemento');

            if(! $firstElement = $elements->first())
                mori('nessun elemento');

            $element = $elements->firstWhere(
                $firstElement->getKeyName(),
                request()->rowId
            );

            $collection = collect();
            $collection->push($element);

            $this->setElements($collection);
            $this->setData();

            return $this;
        }


        mori('non instanceof Closure, vuol dire che è una query o una collection, zio culo culo culo culo cazzo culo cazzo culo merda');
    }

    static function createStandAloneTable(array $parameters)
    {
        $name = $parameters['name'];
        $fieldsGroups = $parameters['fieldsGroups'] ?? 'index';
        $elements = $parameters['elements'];
        $selectRowCheckboxes = $parameters['selectRowCheckboxes'] ?? false;
        $extraVariables = $parameters['extraVariables'] ?? null;
        $modelClass = $parameters['modelClass'] ?? null;

        $table = static::create(
            $name,
            $fieldsGroups,
            function() use($elements)
            {
                return $elements;
            },
            $selectRowCheckboxes,
            $extraVariables,
            $modelClass
        );

        if((request()->ajax())&&(request()->model))
            return $table;

        $table->setArrayTable();

        unset($parameters['fieldsGroups']);

        $table->bind($parameters);

        return $table;
    }

    static function create(
        string $name,
        array $fieldsGroups,
        $elements,
        bool $selectRowCheckboxes = false,
        array $extraVariables = null,
        string $modelClass = null
    )
    {
        $table = new static();

        $table->setMainModelElement($modelClass);

        $table->cleanCachedTableKeyParameterIfEditor();

        if($selectRowCheckboxes)
            $table->setRowSelectCheckboxes();

        $table->setVariables($extraVariables ?? []);

        if(request()->ajax())
        {
            if(
                ($table->cachedTableKey = request()->input('cachedtablekey'))
                &&($data = cache()->pull($table->cachedTableKey))
            )
            {
                $table->setData($data, $selectRowCheckboxes);

                return $table;
            }

            $table->addFieldsGroups($fieldsGroups);
            // $table->addFields($fields);

            if(request()->rowId)
                return $table->returnSingleElement($elements);
            
            $elements = $elements();
            $table->setElements($elements);
            $table->setData();

            return $table;
        }
        // return "HTTP";

        $table->addFieldsGroups($fieldsGroups);

        $table->setName($name);
        $table->setUrl(request()->url());

        $table->setElements($elements());

        return $table;
    }

    public function hasInlineSearch()
    {
        return true;
    }

    public function setUrl(string $url)
    {
        $this->url = $url;
    }

    public function getUrl()
    {
        return $this->url;
    }

    public function setElements(Collection $elements = null)
    {
        $this->elements = $elements;
    }

    public function setName(string $name)
    {
        $this->name = $name;

        $this->setCachedTableKey();
    }

    public function getName()
    {
        if($this->name ?? false)
            return $this->name;

        $this->setName('table' . rand(0, 99999));

        return $this->getName();
    }

    public function getCamelName()
    {
        return Str::camel($this->getName());
    }

    public function getStripeClass()
    {
        if($this->stripe)
            return 'stripe row-border';
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
        if((request()->ajax()) && (! request()->ibFetcher))
            return
            [
                "draw" => 1,
                "recordsTotal" => count($this->getData()),
                "recordsFiltered" => count($this->getData()),
                "data" => $this->getData()
            ];

        $this->prepareCachedData();
        $this->parseColumnDefs();

        $this->parseOptions();

        return view('datatables::table', ['table' => $this]);
    }

    public function renderPortion()
    {
        $this->parseColumnDefs();
        $this->parseOptions();

        return view('datatables::_table', [
            'table' => $this,
            'tableSourceData' => $this->calculateData()
        ])->render();        
    }

    public function render()
    {
        $this->parseColumnDefs();

        return view('datatables::_table', ['table' => $this]);
    }

    public function getId()
    {
        return $this->id ?? $this->cachedTableKey;
    }

    public function setArrayTable()
    {
        $this->sourceType = 'array';
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

    //TODO PROTEGGERE QUESTA CON DEI FILLABLE???
    public function bind(array $parameters)
    {
        foreach($parameters as $key => $value)
            $this->{$key} = $value;
    }

    public function canScrollX()
    {
        return $this->scrollX;
    }

    public function getRelationName()
    {
        return $this->getName();
    }
}

