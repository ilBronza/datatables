<?php

namespace IlBronza\Datatables;

use IlBronza\Datatables\Traits\DatatableColumnDefsTrait;
use IlBronza\Datatables\Traits\DatatableDataTrait;
use IlBronza\Datatables\Traits\DatatableFieldsTrait;
use App\Models\Appointment;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class Datatables
{
    use DatatableDataTrait;
    use DatatableFieldsTrait;
    use DatatableColumnDefsTrait;

    public $fields;
    public $fieldsGroups;
    public $elements;
    public $url;

    public function __construct()
    {
        $this->fields = collect();
        $this->fieldsGroups = collect();
    }

    static function create(string $name, array $fieldsGroups, Collection $elements)
    {
        if(request()->ajax())
        {
            if($cachedTableKey = request()->input('cachedtablekey'))
            {
                $table = new Datatable();

                if($data = cache()->pull($cachedTableKey))
                {
                    $table->setData($data);

                    return $table;
                }

                $table->addFieldsGroups($fieldsGroups);
                // $table->addFields($fields);

                $elements = Appointment::with('contact')->get();
                $table->setElements($elements);
                $table->setData();

                return $table;
            }

            return [
                'status' => 500
            ];
        }
        // return "HTTP";


        $table = new static;

        $table->addFieldsGroups($fieldsGroups);

        $table->setName($name);
        $table->setUrl(request()->url());

        $table->setElements($elements);
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

    public function getNextFieldIndex()
    {
        $index = -1;

        foreach($this->fields as $field)
            if($field->index > $index)
                $index = $field->index;

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

        return view('layouts.app', ['table' => $this]);
    }

    public function render()
    {
        $this->parseColumnDefs();

        return view('datatables._table', ['table' => $this]);
    }

    public function getId()
    {
        return $this->id ?? $this->cachedDataKey;
    }

}

