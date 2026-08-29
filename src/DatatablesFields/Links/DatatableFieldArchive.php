<?php

namespace IlBronza\Datatables\DatatablesFields\Links;

class DatatableFieldArchive extends DatatableFieldAjax
{
	public ? string $translationPrefix = 'datatables::fields';
	public ? string $forcedStandardName = 'archive';

    public $faIcon = 'box-archive';
    public $confirmMessage = 'datatables::messages.areYouSureToDeleteThisObject';
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
