<?php

namespace IlBronza\Datatables\Traits\DatatablesFields;

use Illuminate\Support\Str;

trait DatatablesFieldsDisplayTrait
{
    public function getDefaultWidth()
    {
        return $this->defaultWidth ?? false;
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

    public function getHeaderHtmlClasses()
    {
        if(! $this->isSortable())
            $this->headerHtmlClasses[] = 'no-sort';

        return implode(" ", $this->headerHtmlClasses);
    }
    
    public function addHtmlClass(string $htmlClass)
    {
        $this->htmlClasses[] = $htmlClass;
    }

    public function setHtmlClasses()
    {
        if($this->hasTooltip())
            $this->addHtmlClass('tooltip');
    }

    public function getHtmlClassesString()
    {
        return implode(" ", $this->htmlClasses);        
    }

    public function getHtmlClass()
    {
        $this->addHtmlClass(Str::camel(str_replace(".", " ", $this->name)));

        return $this->getHtmlClassesString();
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
