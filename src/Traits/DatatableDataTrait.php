<?php

namespace ilBronza\Datatables\Traits;

use ilBronza\Datatables\DatatablesFields\DatatableField;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

trait DatatableDataTrait
{
    public function prepareCachedData()
    {
        return cache()->remember(
            $this->getCachedDataKey(),
            300,
            function()
            {
                return $this->calculateData();
            }
        );
    }

    public function setData(array $data = null)
    {
        $this->data = $data ?? $this->calculateData();
    }

    public function getData()
    {
        return $this->data;
    }

    private function getCellDataValue(string $fieldName, Model $element)
    {
        $properties = explode(".", $fieldName);

        do {
            $property = array_shift($properties);

            if(strpos($property, 'mySelf') === false)
                $element = $element->$property?? false;

        } while (count($properties));

        return $element;
    }

    private function getCellData(DatatableField $field, Model $element)
    {
        $value = $this->getCellDataValue($field->name, $element);

        return $field->transformValue($value);
    }

    private function getCellDataWithSummary(DatatableField $field, Model $element)
    {
        $value = $this->getCellDataValue($field->name, $element);

        return $field->transformValueWithSummary($value);
    }

    public function calculateData()
    {
        if($this->hasSummary())
            return $this->calculateDataWithSummary();

        return $this->_calculateData();
    }

    public function calculateDataWithSummary()
    {
        $data = [];

        foreach($this->elements as $element)
        {
            $row = [];

            foreach($this->getFields() as $field)
                $row[] = $this->getCellDataWithSummary($field, $element);

            $data[] = $row;
        }

        $summaryRow = [];

        foreach($this->getFields() as $field)
            $summaryRow[] = $field->getSummaryResult();

        $data[] = $summaryRow;

        return $data;        
    }

    public function _calculateData()
    {
        $data = [];

        foreach($this->elements as $element)
        {
            $row = [];

            foreach($this->getFields() as $field)
                $row[] = $this->getCellData($field, $element);

            $data[] = $row;
        }

        return $data;
    }

    private function setCachedDataKey()
    {
        $this->cachedDataKey = $this->name . Str::random();        
    }

    public function getCachedDataKey()
    {
        return $this->cachedDataKey;
    }


}