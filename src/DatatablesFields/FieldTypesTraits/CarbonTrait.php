<?php

namespace IlBronza\Datatables\DatatablesFields\FieldTypesTraits;

trait CarbonTrait
{
    public function getDateFormat()
    {
    	return $this->dateFormat;
    }

    public function getInputFieldDefaultDateFormat()
    {
        return $this->inputFieldDefaultFormat;        
    }	
}