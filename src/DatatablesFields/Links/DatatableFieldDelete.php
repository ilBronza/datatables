<?php

namespace IlBronza\Datatables\DatatablesFields\Links;

class DatatableFieldDelete extends DatatableFieldAjax
{
	public ? string $translationPrefix = 'datatables::fields';

    public $icon = 'trash';
    public $confirmMessage = 'datatables::messages.areYouSureToDeleteThisObject';
    public $textParameter = false;
	public $dataAttributes = [
		'type' => 'DELETE'
	];

	public function transformValue($value)
	{
		if(! $this->textParameter)
			return $value->getDeleteUrl();

		return [
			$value->getDeleteUrl(),
			$value->{$this->textParameter}
		];
	}
}
