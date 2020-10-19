<?php

namespace IlBronza\Datatables;

class DatatableFieldsGroup
{
    public $name;
    public $fields;

    public function __construct(string $name)
    {
        $this->name = $name;
        $this->fields = collect([]);
    }

    public function setAbsoluteIndexes(int $base = 0)
    {
        foreach($this->fields as $field)
            $field->setAbsoluteIndex($base + $field->index);

        return $this->getNextIndex() + $base;
    }

    public function getNextIndex()
    {
        if($higherField = $this->fields->sortByDesc('index')->first())
            return $higherField->index + 1;

        return 0;
    }

    public function getIndexesArray()
    {
        return $this->fields->pluck('absoluteIndex')->toArray();
    }


    /**
     * create  a field class instance and add it to the fields collection
     *
     * @param string $name
     * @param array $parameters
     */
    public function addFieldByParameters(string $name, array $parameters)
    {
        $field = new DatatableField($name, $parameters);
        $field->setIndex($this->getNextIndex());
        $field->table = $this->table;

        $this->fields->push($field);
    }

    /**
     * check if fieldParameters are just a view name,
     * if true set it as array key => value
     *
     * @param string|array $fieldParameters
     *
     * @throw \Exception if is not string or array
     *
     * @return array
     */
    private function checkIfViewName($fieldParameters)
    {
        if(is_array($fieldParameters))
            return $fieldParameters;

        //if is just the view, set it as view parameter
        if(is_string($fieldParameters))
            return ['view' => $fieldParameters];

        throw new \Exception('wrong field parameteres for table ' . json_encode($this->name));
    }

    /**
     * populate each field with correct parameters, at least with view as view name by default
     */
    public function populateFields(array $fieldsGroup)
    {
        foreach($fieldsGroup as $fieldName => $fieldParameters)
        {
            //if $fieldParameters is just view name, set it to array ['view' => 'viewName']
            $fieldParameters = $this->checkIfViewName($fieldParameters);

            $this->addFieldByParameters($fieldName, $fieldParameters);
        }
    }
}
