<?php

namespace IlBronza\Datatables\Traits\DatatablesFields;

use IlBronza\Datatables\DatatablesFields\DatatableField;
use Illuminate\Support\Str;

trait DatatablesFieldsElementTrait
{
    public function requiresPlaceholderElement()
    {
        return $this->requiresPlaceholderElement;
    }

    public function setPlaceholderElement()
    {
        if(! $this->requiresPlaceholderElement())
            return ;

        if($this->parent ?? false)
            return $this->placeholderElement = $this->parent->provideChildPlaceholderElement($this);

    	$this->placeholderElement = $this->table->getPlaceholderElement();
    }

    public function provideChildPlaceholderElement(DatatableField $childField)
    {
        $placeholderElement = $this->getPlaceholderElement();

        $pieces = explode(".", $childField->name);

        $property = array_shift($pieces);

        return $placeholderElement->{$property}()->make();
    }

    public function getPlaceholderElement()
    {
        return $this->placeholderElement;
    }
}