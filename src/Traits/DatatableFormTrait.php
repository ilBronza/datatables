<?php

namespace IlBronza\Datatables\Traits;

use Illuminate\Support\Str;

trait DatatableFormTrait
{
    public function getDragAndDropFieldByName(string $columnIntestation)
    {
        foreach($this->getFields() as $field)
            if($field->name == $columnIntestation)
                return $field;

        throw new \Exception('column intestation not found');
    }

    public function setDragAndDropColumnIntestation($columnIntestation)
    {
        $dragAndDropField = $this->getDragAndDropFieldByName($columnIntestation);

        $this->setDragAndDropColumn($dragAndDropField->index);

        if(empty($this->dragAndDrop->selector))
            $this->dragAndDrop->selector = 'td.' . $dragAndDropField->getHtmlClass();
    }

    public function setDragAndDropSelector($columnIntestation)
    {
        $dragAndDropField = $this->getDragAndDropFieldByName($columnIntestation);

        $this->dragAndDrop->selector = 'td.' . $dragAndDropField->getHtmlClass();
    }

    public function setDragAndDropColumn($column)
    {
        $this->createDragAndDrop();

        $this->dragAndDrop->dataSrc = $column;
    }

    public function setDragAndDropUrl($url)
    {
        $this->createDragAndDrop();

        $this->dragAndDrop->url = $url;
    }

    public function setDragAndDropWholeRow($active = true)
    {
        $this->createDragAndDrop();

        $this->dragAndDrop->selector = 'tr';
    }

    public function createDragAndDrop()
    {
        if(isset($this->dragAndDrop))
           return true;

        $this->dragAndDrop = (object) [];
    }
}