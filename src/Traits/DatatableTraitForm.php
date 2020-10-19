<?php

namespace IlBronza\Datatables\Traits;

trait DatatableTraitForm
{
    // public function setDragAndDropColumnIntestation($columnIntestation)
    // {

    //  $intestations = array_keys($this->fields);
    //  if(($column = array_search($columnIntestation, $intestations)) === false)
    //      throw new \Exception('column intestation not found');

    //  $this->setDragAndDropColumn($column + 1);

    //  if(!isset($this->dragAndDrop->selector))
    //      $this->dragAndDrop->selector = 'td.' . $columnIntestation;
    // }

    // public function setDragAndDropColumn($column)
    // {
    //  $this->createDragAndDrop();

    //  $this->dragAndDrop->dataSrc = $column;
    // }

    // public function setDragAndDropUrl($url)
    // {
    //  $this->createDragAndDrop();

    //  $this->dragAndDrop->url = $url;
    // }

    // public function setDragAndDropWholeRow($active = true)
    // {
    //  $this->createDragAndDrop();

    //  $this->dragAndDrop->selector = 'tr';
    // }

    // public function createDragAndDrop()
    // {
    //  if(isset($this->dragAndDrop))
    //      return true;

    //  $this->dragAndDrop = (object) [];
    // }

    // public function addButton($button)
    // {
    //  $this->buttons[] = $button;
    // }

    // public function addBottomLink(string $name, string $text, string $href, array $params = [])
    // {
    //  $this->extraLinks[$name] = new LinkButton($name, $text, $href, $params);
    // }


    // public function setButtons($buttons)
    // {
    //  $this->buttons = $this->modelClass::getTableFields($buttons);

    //  //if old kind of table, flatten it
    //  if(isset($this->buttons['buttons']))
    //      $this->buttons = $this->buttons['buttons'];

    //  elseif(isset($this->buttons['topButtons']))
    //      $this->buttons = $this->buttons['topButtons'];
    // }



    // public function setSubmitText(string $text, array $data = [])
    // {
    //  $this->form->submitText = trans($text, $data);
    // }

    // public function hideCancelButton()
    // {
    //  $this->form->hideCancelButton = true;
    // }

    // public function setFormRoute($route, $routeParams = [], $type = 'post')
    // {
    //  $this->form = (object) ['type', 'action'];
    //  $this->form->type = $type;
    //  $this->form->action = route($route, $routeParams);
    // }

    // public function hideDefaultSubmit(bool $set = true)
    // {
    //  $this->hideDefaultSubmit = $set;
    // }


    // public function addSubmit(string $name, string $text, array $params = [])
    // {
    //  $this->extraSubmits[$name] = new SubmitButton($name, $text, $params);
    // }

}