<?php

namespace IlBronza\Datatables\Traits;

trait DatatableTraitSetterGetter
{
    /**
     * set table id
     *
     * @param string $id
     */
    public function setId(string $id)
    {
        $this->id = $id;
    }

    /**
     * set the selected elements index
     *
     * @param array $selected
     */
    public function setSelecteElementsIndexes($selected = [])
    {
        $this->selectedElements = $selected;
    }

    /**
     * set a parameter by name and value
     *
     * @param string $parameter,
     * @param mixed $value,
     *
     */
    public function set(string $parameter, $value)
    {
        $this->$parameter = $value;
    }

    /**
     * setDom definition
     *
     * @param string $dom
     */
    public function setDom(string $dom)
    {
        $this->dom = $dom;
    }


    // public function setName($name, bool $show = null)
    // {
    //  $this->showName($show);

    //  $this->name = $name;

    //  $this->getSessionSelected();
    // }

    // public function setFooterSearch($check = true)
    // {
    //  $this->footerSearch = $check;  
    // }

    // public function hideFooter()
    // {
    //  $this->setFooterSearch(false);
    // }



    // public function getSessionSelected()
    // {

    //  // mori(session('manzotingna'));
    //  $currentUrlTable = 'table.' . str_slug(url()->current());

 //        // if(!$table = session('table.' . $this->name))
 //        if(!$table = session($currentUrlTable))
 //            return false;

 //        if($table->updated_at->diffInMinutes() > 15)
 //            return false;

 //        $this->selected = $table->selected;
    // }

    // public function setTitle($title)
    // {
    //  $this->title = $title;
    // }

    // public function setTranslatedTitle($title)
    // {
    //  $this->translatedTitle = $title;
    // }

    public function setElements($elements)
    {
        $this->elements = $elements;
    }


}