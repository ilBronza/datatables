<?php

namespace IlBronza\Datatables\Traits;

trait DatatableTraitViews
{
    // public function addView($view, $data = [])
    // {
    //  $this->views[] = [$view => $data];
    // }

    // public function addFooterView($view, $data = [])
    // {
    //  $this->footerViews[] = [$view => $data];
    // }



    // public function addViewParameter($name, $value)
    // {
    //  $this->viewParameters[$name] = $value;
    // }

    // public function addViewParameters(array $parameters)
    // {
    //  foreach($parameters as $name => $value)
    //      $this->addViewParameter($name, $value);
    // }

    // public function shareViewsData()
    // {
    //  foreach($this->views as $data)
    //      foreach($data[key($data)] as $name => $values)
    //          $this->viewParameters[$name] = $values;
    //          // view()->share($name, $values);

    //  foreach($this->footerViews as $data)
    //      foreach($data[key($data)] as $name => $values)
    //          $this->viewParameters[$name] = $values;
    //          // view()->share($name, $values);
    // }

    // public function shareViewData()
    // {
    //  if(empty($this->id))
    //      $this->id = $this->name;

    //  $this->shareViewsData();

    //  // $this->viewParameters[$this->name] = $this;

    //  view()->share($this->name, $this);
    // }

    // public function shareSingleViewData()
    // {
    //  if(empty($this->id))
    //      $this->id = $this->name;

    //  $this->shareViewsData();

    //  // $this->viewParameters['table'] = $this;
    //  // view()->share('table', $this);
    // }


}