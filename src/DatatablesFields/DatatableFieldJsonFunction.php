<?php

namespace IlBronza\Datatables\DatatablesFields;

class DatatableFieldJsonFunction extends DatatableField
{
	public function getFunction()
	{
		return $this->function;
	}

    public function transformValue($value)
    {
    	if(! $value)
    		return ;

        return json_encode($value->{$this->getFunction()}());
    }
}