<?php

namespace IlBronza\Datatables\Traits\DatatablesFields;

use Illuminate\Support\Str;

trait DatatablesFieldsDisplayTrait
{
    public function getTranslatedName()
    {
        return __('fields.' . $this->name);
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

    public function getHtmlClass()
    {
        $this->addHtmlClass(Str::camel(str_replace(".", " ", $this->name)));

        return implode(" ", $this->htmlClasses);
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
