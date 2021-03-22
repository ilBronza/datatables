<?php

namespace IlBronza\Datatables\DatatablesFields\Links;

class DatatableFieldAjax extends DatatableFieldLink
{
	public $dataAttributes = [
		'type' => 'POST'
	];

    public $actionHtmlClass = 'ib-cell-ajax-button';

	public function getHtmlClassesAttributeString()
	{
		$this->addHtmlClass(
			$this->actionHtmlClass
		);

		return parent::getHtmlClassesAttributeString();
	}
}
