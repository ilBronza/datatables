<?php

namespace IlBronza\Datatables\Traits\DatatablesFields;

trait DatatablesFieldsFiltersTrait
{
    public function hasRangeFilter()
    {
        return !! $this->rangeFilter;
    }

    public function getFilterType()
    {
        return $this->filterType ?? $this->defaultFilterType;
    }

    public function getrangeFilterType()
    {
        if($this->rangeFilter === true)
            return 'normal';

        return $this->rangeFilter;
    }

    public function getRangeFilterJavascriptPlugin(string $tableId = null)
    {
        $view = 'datatables::datatablesFields.filters.scripts._range' . ucfirst($this->getrangeFilterType());

        return view($view, [
            'tableId' => $tableId,
            'field' => $this
        ])->render();
    }

}
