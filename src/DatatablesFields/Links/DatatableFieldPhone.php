<?php

namespace IlBronza\Datatables\DatatablesFields\Links;

class DatatableFieldPhone extends DatatableFieldLink
{
	public $faIcon = 'phone';

	public function transformValue($value)
	{
		return $value;
	}

	public function getCustomColumnDefSingleResult()
	{
		return "
			if(item)
				item = '<a " . $this->getHtmlDataAttributesString() . " " . $this->getHtmlClassesAttributeString() . " " . $this->getTargetHtml() . " href=\"callto:' + item + '\">" . $this->getIconHtml() . "' + item + '</a>';

			else item = '';
		";
	}
}
