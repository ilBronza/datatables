<?php

namespace IlBronza\Datatables\Traits;

use IlBronza\Buttons\Button;
use IlBronza\Form\Form;
use IlBronza\FormField\FormField;
use Illuminate\Support\Str;

use function get_class_methods;

trait DatatableFormTrait
{
	public function provideForm() : Form
	{
		$this->form = new Form();

		$this->form->setHasSubmitButton(false)->setCancelButton(false);

		return $this->form;
	}

	public function hasForm() : bool
	{
		return !! $this->form;
	}

	public function getForm() : Form
	{
		if($this->form)
			return $this->form;

		return $this->provideForm();
	}

	public function addPostField(FormField $formField) : self
	{
		$this->getForm()->addFormField($formField);

		return $this;
	}

	public function createBulkEditButton(array $parameters)
	{
		$button = $this->_createPostButton($parameters);

		$button->setAsIframe();

		$this->getForm()->addClosureButton($button);
	}

	public function _createPostButton(array $parameters) : Button
	{
		$button = Button::create($parameters);

		$button->setPrimary();

		$button->setData('tableid', $this->getId());

		$button->setSubmitTableButton();

		return $button;
	}

	public function createPostButton(array $parameters) : Button
	{
		$button = $this->_createPostButton($parameters);

		// if($button->name == 'products::buttons.addRows')
		// 	dd($button);

		$this->getForm()->addClosureButton($button);

		return $button;
	}

	public function createPostButtonSamePage(array $parameters) : Button
	{
		$button = $this->createPostButton($parameters);

		$button->setRedirectSubmit(false);

		return $button;
	}

	public function getPostFields() : array
	{
		return $this->postFields;
	}
    public function getDragAndDropFieldByName(string $columnIntestation)
    {
        foreach($this->getFields() as $field)
            if($field->name == $columnIntestation)
                return $field;

        throw new \Exception('column intestation "' . $columnIntestation . '" not found. Check existence and permissions');
    }

    public function setDragAndDropColumnIntestation($columnIntestation)
    {
        $dragAndDropField = $this->getDragAndDropFieldByName($columnIntestation);

        $this->setDragAndDropColumn($dragAndDropField->index);

        if(empty($this->dragAndDrop->selector))
            $this->dragAndDrop->selector = 'td.' . $dragAndDropField->getHtmlClassForCss();
    }

    public function setDragAndDropSelector($columnIntestation)
    {
        $dragAndDropField = $this->getDragAndDropFieldByName($columnIntestation);

        $this->dragAndDrop->selector = 'td.' . $dragAndDropField->getHtmlClassForCss();
    }

	public function setDragAndDropStoringReorderUrl(string $url)
	{
		$this->dragAndDrop->url = $url;
	}

    public function setDragAndDropColumn($column)
    {
        $this->createDragAndDrop();

        $this->dragAndDrop->dataSrc = $column;
    }

    public function setDragAndDropUrl($url)
    {
        $this->createDragAndDrop();

        $this->dragAndDrop->url = $url;
    }

    public function setDragAndDropWholeRow($active = true)
    {
        $this->createDragAndDrop();

        $this->dragAndDrop->selector = 'tr';
    }

    public function createDragAndDrop()
    {
        if(isset($this->dragAndDrop))
           return true;

        $this->dragAndDrop = (object) [];
    }
}