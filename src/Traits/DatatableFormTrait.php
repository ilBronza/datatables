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

	public function createPostButton(array $parameters)
	{
		$button = Button::create($parameters);

		$button->setPrimary();
		
		$button->setData('tableid', $this->getId());

		$button->setSubmitTableButton();

		$this->getForm()->addClosureButton($button);
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