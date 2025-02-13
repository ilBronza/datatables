<?php

namespace IlBronza\Datatables\DatatablesFields\Dates;

use IlBronza\Datatables\DatatablesFields\DatatableField;
use IlBronza\Datatables\DatatablesFields\FieldTypesTraits\CarbonTrait;

class DatatableFieldCarbon extends DatatableField
{
    use CarbonTrait;

    public $defaultFilterType = 'date';
    public $defaultWidth = '80px';

	public $rangeFilter = true;

    public function transformValue($value)
    {
        return $value->timestamp ?? null;
    }

	public function getCompiledAsRowClassScript()
	{
		if ($this->compiledAsRowClass)
			return '
        //' . $this->name . "
        
        if(data[" . $this->getIndex() . "] != null)
            $(row).addClass('" . $this->getCompiledAsRowClassPrefix() . "compiled');
        else
            $(row).addClass('" . $this->getCompiledAsRowClassPrefix() . "notcompiled');
        ";
	}
}