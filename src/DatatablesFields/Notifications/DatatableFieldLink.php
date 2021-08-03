<?php

namespace IlBronza\Datatables\DatatablesFields\Notifications;

use IlBronza\Datatables\DatatablesFields\Links\DatatableFieldLink as BaseDatatableFieldLink;

class DatatableFieldLink extends BaseDatatableFieldLink
{
	public $icon = 'link';
	public $textParameter = true;
	public $dataAttributes = [];

	public function transformValue($value)
	{
		return [
			$value->getLink(),
			$value->getLinkText()
		];
	}

	public function getCustomColumnDefSingleResult()
	{
		return "
			if(item)
				item = '<a " . $this->getHtmlDataAttributesString() . " " . $this->getHtmlClassesAttributeString() . " " . $this->getTargetHtml() . " href=\"' + " . $this->getLinkUrlString() . " + '\">" . $this->getIconHtml() . "' + " . $this->getLinkTextString() . " + '</a>';

			else item = '';
		";
	}
}
