<?php

namespace IlBronza\Datatables\DatatablesFields\Links;

use IlBronza\Datatables\DatatablesFields\Links\DatatableFieldLink;

class DatatableFieldLinkCachedProperty extends DatatableFieldLink
{
    public $defaultWidth = '120px';
    public $property;

    public function isSortable()
    {
    	return true;
    }

	public function transformValue($value)
	{
		if(! $model = $this->modelClass::findCached($value))
			return [
				'#',
				'null'
			];

		return [
			$model->getShowUrl(),
			$model->{$this->property}
		];
	}

	public function getLinkUrlString()
	{
		return "item[0]";
	}

	public function getLinkTextString()
	{
		return "item[1]";
	}

	public function getCustomColumnDefSingleResult()
	{
		return "

			if(item)
			{
				item = '<" . $this->getHtmlTagString() . $this->getParentDataIndexString() . $this->getHtmlDataAttributesString() . " " . $this->getHtmlClassesAttributeString() . " " . $this->getTargetHtml() . " href=\"' + " . $this->getLinkUrlString() . " + '\">" . $this->getPrefix() . "" . $this->getIconHtml() . "' + " . $this->getLinkTextString() . " + '" . $this->getSuffix() . "</" . $this->getHtmlTagString() . ">';
			}

			else item = '';
		";
	}
}
