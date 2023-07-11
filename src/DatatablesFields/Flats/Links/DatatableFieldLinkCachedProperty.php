<?php

namespace IlBronza\Datatables\DatatablesFields\Flats\Links;

use IlBronza\Datatables\DatatablesFields\Links\DatatableFieldLink;

class DatatableFieldLinkCachedProperty extends DatatableFieldLink
{
    public $property;
    public $function;

	public function transformValue($value)
	{
		if(! $model = $this->modelClass::findCached($value))
			return null;

		return "<" . $this->getHtmlTagString() . $this->getParentDataIndexString() . $this->getHtmlDataAttributesString() . " " . $this->getHtmlClassesAttributeString() . " " . $this->getTargetHtml() . " href=\"' + " . $model->{$this->function}() . " + '\">" . $this->getPrefix() . "" . $this->getIconHtml() . " " . $model->{$this->property} . " " . $this->getSuffix() . "</" . $this->getHtmlTagString() . ">";
	}
}
