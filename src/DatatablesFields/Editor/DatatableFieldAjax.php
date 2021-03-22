<?php

namespace IlBronza\Datatables\DatatablesFields\Editor;

class DatatableFieldAjax extends DatatableFieldEditor
{
	public $width = '25px';
	public $htmlClasses = [
		'ib-ajax'
	];
	public $parameter = false;

	public function getCustomColumnDefSingleResult()
	{

		return "
			" . $this->substituteUrlParameter() . "

			item = '<span " . $this->getHtmlDataAttributesString() . $this->getHtmlClassesAttributeString() . " >" . $this->getIconHtml() . "</span>';
		";
	}
}