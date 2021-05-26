<?php

namespace IlBronza\Datatables\DatatablesFields\Links;

class DatatableFieldArchive extends DatatableFieldAjax
{
    public $icon = 'album';
    //public $confirmMessage = 'messages.areYouSureToDeleteThisObject';
    public $textParameter = false;
	public $dataAttributes = [
		'type' => 'PUT'
	];

	public function transformValue($value)
	{
		if(! $this->textParameter)
			return $value->getArchiveUrl();

		return [
			$value->getArchiveUrl(),
			$value->{$this->textParameter}
		];
	}
}
