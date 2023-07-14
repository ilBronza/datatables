<?php

namespace IlBronza\Datatables\DatatablesFields\Links;

use IlBronza\Datatables\DatatablesFields\Links\DatatableFieldLink;

class DatatableFieldLinkCachedProperty extends DatatableFieldLink
{
    public $defaultWidth = '120px';
    public $property;
    public $nullValue;
    public $function = 'getShowUrl';

    public function isSortable()
    {
    	return true;
    }

    public function getNullValue()
    {
    	return $this->nullValue;
    }

	public function transformValue($value)
	{
		if(! $value)
			return [
				'#',
				$this->getNullValue()
			];
		
		if(! $model = $this->modelClass::findCached($value))
			return [
				'#',
				$this->getNullValue()
			];

		return [
			$model->{$this->function}(),
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
