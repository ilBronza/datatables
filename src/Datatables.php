<?php

namespace IlBronza\Datatables;

use App\Models\Appointment;
use IlBronza\Datatables\Traits\DatatableButtonsTrait;
use IlBronza\Datatables\Traits\DatatableColumnDefsTrait;
use IlBronza\Datatables\Traits\DatatableDataTrait;
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
    use DatatableFormTrait;
    use DatatableButtonsTrait;
    use DatatableDataTrait;
    use DatatableFieldsTrait;
    use DatatableColumnDefsTrait;
    use DatatablesExtraViewsTrait;
    use DatatableOptionsTrait;
    use DatatableSelectRowsTrait;

    public $rowId;
    public $fields;
    public $fieldsGroups;
    public $elements;
    public $url;
    public $columnDefs = [];
    public $buttons = ['copy', 'csv'];
    public $getRowIdIndex = false;
    public $stripe = true;
    public $options = [];

    public function __construct()
    {
        $this->fields = collect();
        $this->fieldsGroups = collect();

        $this->customButtons = collect();
    }

    static function create(string $name, array $fieldsGroups, $elements, bool $selectRowCheckboxes = false)
    {
        $table = new static();

        if($selectRowCheckboxes)
            $table->setRowSelectCheckboxes();

        if(request()->ajax())
        {
            if($cachedTableKey = request()->input('cachedtablekey'))
            {
                if($data = cache()->pull($cachedTableKey))
                {
                    $table->setData($data, $selectRowCheckboxes);

                    return $table;
                }

                $table->addFieldsGroups($fieldsGroups);
                // $table->addFields($fields);

                $elements = $elements();
                $table->setElements($elements);
                $table->setData();

                return $table;
            }

            return [
                'status' => 500
            ];
        }
        // return "HTTP";

        $table->addFieldsGroups($fieldsGroups);

        $table->setName($name);
        $table->setUrl(request()->url());

        $table->setElements($elements());
        $table->prepareCachedData();

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

    public function setElements(Collection $elements)
    {
        $this->elements = $elements;
    }

    public function setName(string $name)
    {
        $this->name = $name;

        $this->setCachedDataKey();
    }

    public function getName()
    {
        return $this->name;
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
        if(request()->ajax())
            return
            [
                "draw" => 1,
                "recordsTotal" => count($this->getData()),
                "recordsFiltered" => count($this->getData()),
                "data" => $this->getData()
            ];

        $this->parseColumnDefs();

        $this->parseOptions();

        return view('datatables::table', ['table' => $this]);
    }

    public function render()
    {
        $this->parseColumnDefs();

        return view('datatables::_table', ['table' => $this]);
    }

    public function getId()
    {
        return $this->id ?? $this->cachedDataKey;
    }

}

