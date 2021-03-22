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

    public function setHtmlClasses(array $parameters = [])
    {
        if($this->hasTooltip())
            $this->addHtmlClass('tooltip');

        $this->htmlClasses = array_merge(
            $this->htmlClasses,
            $parameters['htmlClasses'] ?? []
        );
    }

    public function getHtmlClasses()
    {
        return array_merge(
            $this->htmlClasses,
            $this->fieldSpecificClasses ?? []
        );
    }

    public function getHtmlClassesString()
    {
        return implode(" ", $this->getHtmlClasses());
    }

    public function getHtmlClassForCss()
    {
        return $this->getCamelName();
    }

    public function getCamelName()
    {
        return Str::camel(str_replace(".", " ", $this->name));
    }

    public function getHtmlClass()
    {
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
