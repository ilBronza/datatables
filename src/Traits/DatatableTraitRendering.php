<?php

namespace IlBronza\Datatables\Traits;

trait DatatableTraitRendering
{
    /**
     * tells if table allow select function
     */
    public function allowsColumnReorder()
    {
        return $this->columnReorder;
    }

    /**
     * tells if table allow select function
     */
    public function usesSelect()
    {
        return $this->select;
    }

    /**
     * tells if table must show title
     */
    public function mustShowTitle()
    {
        return $this->showTitle;
    }

    /**
     * tells if table must show title
     */
    public function mustShowColumnToggler(bool $condition = null)
    {
        if(is_null($condition))
            return $this->showColumnToggler;

        $this->showColumnToggler = $condition;
    }

    /**
     * tells if table must show utility buttons (csv and copy)
     */
    public function mustShowUtilityButtons(bool $condition = null)
    {
        if(is_null($condition))
            return $this->showUtilityButtons;

        $this->showUtilityButtons = $condition;
    }


    /**
     * tells if table must show title
     */
    public function mustShowRowToggler()
    {
        return $this->showRowToggler;
    }

    /**
     * tells if table must show title
     */
    public function mustDeferRender()
    {
        return ! $this->mustShowRowToggler();
    }

    /**
     * tells if table must show footer intestations
     */
    public function mustShowFooter()
    {
        return $this->showFooter;
    }

    /**
     * tells if table must show hedaer intestations
     */
    public function mustShowHeader()
    {
        return $this->showHeader;
    }

    /**
     * get translated table title
     */
    public function getTranslatedTitle()
    {
        return trans('tables.' . ($this->title?? $this->name));
    }

    /**
     * get translated table title
     */
    public function setTitle(string $title)
    {
        $this->title = $title;
    }

    /**
     * render html title
     *
     * @param string $tag
     * @param string $class
     */
    public function getHtmlTitle(string $tag = 'span', string $class = 'uk-h3')
    {
        if(! $this->mustShowTitle())
            throw new \Exception('table title not set');

        return "<{$tag} class='{$class}'>{$this->getTranslatedTitle()}</{$tag}>";
    }

    // public function showName(bool $show = null)
    // {
    //  if(isset($show))
    //      return $this->showName;

    //  $this->showName = $show;
    // }

    // public function showCSVExport($show = true)
    // {
    //  $this->csvExport = $show; 
    // }

    // public function showToggler($show = true)
    // {
    //  $this->toggler = $show;
    // }

    // public function hideToggler()
    // {
    //  $this->toggler = false;
    // }

    // public function hideCounters()
    // {
    //  $this->showCounters = false;
    // }

    // public function hideTitle()
    // {
    //  $this->noTitle = true;
    // }

    // public function setLightTable()
    // {
 //        $this->hideTitle();
 //        $this->hideFilters();
 //        $this->hideFooter();
 //        $this->hideToggleColumn();
    // }

    // public function hideFilters()
    // {
    //  $this->noSearch = true;
    // }

    // public function hideToggleColumn()
    // {
    //  $this->noToggleColumn = true;
    // }

    // public function renderName()
    // {
    //  return $this->renderTitle();
    // }



    // public function renderTitle()
    // {
    //  if(isset($this->noTitle))
    //      return false;

    //  if(isset($this->translatedTitle))
    //      return $this->translatedTitle;

    //  return trans('tables.' . ($this->title?? $this->name));
    // }

}