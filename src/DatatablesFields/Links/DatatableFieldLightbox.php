<?php

namespace IlBronza\Datatables\DatatablesFields\Links;

use IlBronza\Datatables\DatatablesFields\Links\DatatableFieldLink;

class DatatableFieldLightbox extends DatatableFieldLink
{
	public function getCustomColumnDefSingleResult()
	{
		return "

			if(item)
			{
				item = '<div uk-lightbox><" . $this->getHtmlTagString() . $this->getParentDataIndexString() . $this->getHtmlDataAttributesString() . " " . $this->getHtmlClassesAttributeString() . " " .
			$this->getTargetHtml() . " href=\"' + " . $this->getLinkUrlString() . " + '?iframed=true\"  data-type=\"iframe\">" . $this->getPrefix() . "" . $this->getIconHtml() . "' + " . $this->getLinkTextString() . " + '" .
			$this->getSuffix() . "</" . $this->getHtmlTagString() . "></div>';
			}

			else item = '';
		";
	}
}
