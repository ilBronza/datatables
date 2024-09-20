<?php

namespace IlBronza\Datatables\DatatablesFields\Links;

class DatatableFieldEmail extends DatatableFieldLink
{
	public bool $truncateText = true;
	public $defaultWidth = '195px';

	public function transformValue($value)
	{
		return $value;
	}

	public function getLinkUrlString()
	{
		return 'item';
	}

	public function getCustomColumnDefSingleResult()
	{
		return "
			if(item)
			{
				item = '<" . $this->getHtmlTagString() . $this->getParentDataIndexString() . $this->getHtmlDataAttributesString() . " " . $this->getHtmlClassesAttributeString() . " " . $this->getTargetHtml() . " href=\"mailto:' + " . $this->getLinkUrlString() . " + '\">" . $this->getPrefix() . "" . $this->getIconHtml() . "' + " . $this->getLinkUrlString() . " + '" . $this->getSuffix() . "</" . $this->getHtmlTagString() . ">';
			}

			else item = '';
		";
	}
}
