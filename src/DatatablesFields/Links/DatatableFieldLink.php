<?php

namespace IlBronza\Datatables\DatatablesFields\Links;

use IlBronza\Datatables\DatatablesFields\DatatableField;
use IlBronza\Datatables\DatatablesFields\FieldTypesTraits\IconTextContentTrait;

class DatatableFieldLink extends DatatableField
{
	use IconTextContentTrait;

	public $icon = false;
	public $textParameter = false;
	public $textMethod = false;
	public $defaultWidth = '45px';
	public $showNull = false;
	public $dataAttributes = [];
	public $htmlTag = 'a';

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
		{
			if(! isset($this->variable))
				return $value->{$this->function}();

			$variableValue = $this->table->getVariable($this->variable);
			return $value->{$this->function}($variableValue);
		}

		if(! isset($this->variable))
			return [
				$value->{$this->function}(),
				$value->{$this->textParameter}
			];

		$variableValue = $this->table->getVariable($this->variable);
			return [
				$value->{$this->function}(),
				$value->{$this->function}($variableValue)
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
		if($this->textParameter)
			return "item[0]";

		if($this->textMethod)
			return "item[0]";

		return "item";
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
