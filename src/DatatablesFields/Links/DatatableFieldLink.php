<?php

namespace IlBronza\Datatables\DatatablesFields\Links;

use IlBronza\Datatables\DatatablesFields\DatatableField;
use IlBronza\Datatables\DatatablesFields\FieldTypesTraits\DataAttributesTrait;
use IlBronza\Datatables\DatatablesFields\FieldTypesTraits\HtmlClassesAttributesTrait;
use IlBronza\Datatables\DatatablesFields\FieldTypesTraits\IconTextContentTrait;

class DatatableFieldLink extends DatatableField
{
	use HtmlClassesAttributesTrait;
	use IconTextContentTrait;
	use DataAttributesTrait;

	public $icon = false;
	public $textParameter = false;
	public $defaultWidth = '45px';
	public $dataAttributes = [];

	/**
	 * return field default width based on text existence or just icon
	 *
	 * if link contains text, default width is null, if is just icon, default width will be 25px
	 *
	 * return mixed
	 */
    public function getWidth()
    {
		if(! $this->textParameter)
        	return $this->defaultWidth;

        return false;
    }

    public function isSortable()
    {
    	if(! $this->sortable)
    		return false;

    	if(! $this->textParameter)
    		return false;

    	return $this->sortable;
    }

    public function getFilterType()
    {
		if(! $this->textParameter)
			return 'none';

		return parent::getFilterType();
    }


	public function transformValue($value)
	{
		if(! $this->textParameter)
			return $value->{$this->function}();

		return [
			$value->{$this->function}(),
			$value->{$this->textParameter}
		];
	}

	public function getTargetHtml()
	{
		if(isset ($this->target))
			return "target=\"{$this->target}\" ";

		return ;
	}

	public function getLinkUrlString()
	{
		if(! $this->textParameter)
			return "item";

		return "item[0]";
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
