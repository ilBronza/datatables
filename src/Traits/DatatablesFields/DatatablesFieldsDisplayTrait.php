<?php

namespace IlBronza\Datatables\Traits\DatatablesFields;

use Illuminate\Support\Str;

trait DatatablesFieldsDisplayTrait
{
    public function canBeHidden() : bool
    {
        if(! $this->table->canHideColumns())
        // if(! $this->table->usesColumnDisplay())
            return false;

        return true;
    }

    public function getDefaultWidth()
    {
        return $this->getWidth() ?? $this->defaultWidth ?? false;
    }

    public function getWidth()
    {
        return $this->width ?? false;
    }

    public function manageWidth(array $parameters)
    {
        if($parameters['width'] ?? false)
            return;

        if($width = $this->getDefaultWidth())
            $this->width = $width;
    }

    public function getTranslatedName()
    {
        return __('fields.' . $this->name);
    }

    public function getJsonAjaxExtraData()
    {
        return json_encode($this->fieldExtraData);
    }

    public function getCamelName()
    {
        return Str::camel(str_replace(".", " ", $this->name));
    }

    public function getSluggedName()
    {
        return Str::slug(str_replace(".", " ", $this->name));
    }

    public function renderHeader()
    {
        return view('datatables::datatablesFields._header', ['field' => $this]);
    }

    public function hasTooltip()
    {
        return $this->tooltip;
    }

}
