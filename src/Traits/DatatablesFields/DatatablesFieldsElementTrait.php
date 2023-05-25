<?php

namespace IlBronza\Datatables\Traits\DatatablesFields;

use IlBronza\Datatables\DatatablesFields\DatatableField;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

trait DatatablesFieldsElementTrait
{
    public function _getKey($value)
    {
        try
        {
            return $value->getKey();
        }
        catch(\Throwable $e)
        {
            if($this->debug())
                throw new \Exception($e);

            return null;
        }
    }

    public function getElementItem() : Model
    {
        if(! $this->requireElement())
            $this->element;

        return $this->getPlaceholderElement();
    }

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

        try
        {
            return $placeholderElement->{$property}()->make();            
        }
        catch(\Exception $e)
        {
            ddd($placeholderElement);
        }
    }

    public function getPlaceholderElement()
    {
        if(empty($this->placeholderElement))
            return $this->table->getPlaceholderElement();
        
        return $this->placeholderElement;
    }
}