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
        if(! $value)
            return null;

        $date = $value->format('Y-m-d'); // QUI: prendi il giorno "di calendario" che vuoi preservare

        return \Carbon\Carbon::createFromFormat('Y-m-d', $date, 'UTC')
            ->startOfDay()
            ->timestamp;
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