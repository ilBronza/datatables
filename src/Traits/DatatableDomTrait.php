<?php

namespace IlBronza\Datatables\Traits;

use Illuminate\Support\Str;
use Jenssegers\Agent\Facades\Agent;

trait DatatableDomTrait
{
    public function hasStickyButtons() : bool
    {
        if((Agent::isMobile())||(Agent::isTablet()))
            return false;

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

    public function setDomMode(string $mode)
    {
        if($mode == 'minimal')
            return $this->setMinimalDom();

        if($mode == 'none')
            return $this->setNoneDom();
    }

    public function setDom(string $dom)
    {
        $this->dom = $dom;
    }

    public function setMinimalDom()
    {
        //Blfritip
        $this->setDom('ftip');
    }

    public function setNoneDom()
    {
        //Blfritip
        $this->setDom('tip');
    }

    public function getCustomDom()
    {
        return $this->dom ?? $this->getBaseDom();
    }


}