<?php

namespace IlBronza\Datatables\Traits\DatatablesFields;

use IlBronza\Form\Form;


trait DatatablesFieldsFormTrait
{
	public function getForm() : ? Form
	{
		if($this->form)
			return $this->form;

		return $this->getTable()->getform();
	}

	public function getFormId() : ? string
	{
		return $this->getForm()->getId();
	}
}
