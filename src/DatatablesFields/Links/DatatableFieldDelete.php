<?php

namespace IlBronza\Datatables\DatatablesFields\Links;

class DatatableFieldDelete extends DatatableFieldLink
{
    public $icon = 'trash';
    public $actionHtmlClass = 'ib-cell-ajax-button';
    public $textParameter = false;
    public $dataAttributes = [
    	'method' => 'DELETE'
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

	public function getHtmlClassesAttribute()
	{
		$this->addHtmlClass(
			$this->actionHtmlClass
		);

		return parent::getHtmlClassesAttribute();
	}
}
