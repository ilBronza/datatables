<?php

namespace IlBronza\Datatables\Traits;

use Illuminate\Support\Str;

trait DatatableDomTrait
{
    public function hasStickyButtons()
    {
        if(! is_null($this->domStickyButtons))
            return $this->domStickyButtons;

        return config('datatables.domStickyButtons');
    }

    public function hasStickyHeader()
    {
        if(! is_null($this->domStickyHeader))
            return $this->domStickyHeader;

        return config('datatables.domStickyHeader');
    }

    public function getDomStickynessDataAttribute()
    {
        if((! $this->hasStickyButtons())&&(! $this->hasStickyHeader()))
            return null;

        $stickyString = ["sis"];

        if($this->hasStickyButtons())
            $stickyString[] = "buttons";

        if($this->hasStickyHeader())
            $stickyString[] = "header";

        return 'data-sticky="' . implode("-", $stickyString) . '"';
    }

    public function getBaseDom()
    {
        $this->hasStickyButtons();
        return '<"sis-buttons-header"<"sis-buttons"B><"uk-clearfix"><"sis-header uk-flex uk-flex-middle uk-child-width-1-3"<"uk-flex uk-flex-middle"ril><"tablecaption"><f>>>tip';
    }

    public function setMinimalDom()
    {
        //Blfritip
        $this->dom = 'ftip';
    }

    public function getCustomDom()
    {
        return $this->dom ?? $this->getBaseDom();
    }


}