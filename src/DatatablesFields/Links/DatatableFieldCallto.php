<?php

namespace IlBronza\Datatables\DatatablesFields\Links;

class DatatableFieldCallto extends DatatableFieldLink
{
	public $icon = 'link';
	public $phoneParameter;

	public $textMethod = false;
	public $defaultWidth = '125px';
	public $dataAttributes = [];
	public $htmlTag = 'a';

	public $fieldSpecificClasses = ['callto'];

	public function isSortable()
	{
		if(! $this->sortable)
			return false;

		if(! $this->phoneParameter)
			return false;

		return $this->sortable;
	}

    public function getFilterType()
    {
		if(! $this->phoneParameter)
			return 'none';

		return parent::getFilterType();
    }

	public function transformValue($value)
	{
		if(! $this->phoneParameter)
		{
			if(! isset($this->variable))
				return $value->{$this->function}();

			$variableValue = $this->table->getVariable($this->variable);
			return $value->{$this->function}($variableValue);
		}
		else
			return $value->{$this->phoneParameter};

		if(! isset($this->variable))
			return $value->{$this->function}();

		$variableValue = $this->table->getVariable($this->variable);
			return $value->{$this->function}($variableValue);
	}

	public function getLinkUrlString()
	{
		return "item";
	}

	public function getCustomColumnDefSingleResult()
	{
		return "
			if(item)
			{
				item = '<" . $this->getHtmlTagString() . $this->getParentDataIndexString() . $this->getHtmlDataAttributesString() . " " . $this->getHtmlClassesAttributeString() . " " . $this->getTargetHtml() . " href=\"callto:' + " . $this->getLinkUrlString() . " + '\">" . $this->getPrefix() . "" . $this->getIconHtml() . "' + " . $this->getLinkUrlString() . " + '" . $this->getSuffix() . "</" . $this->getHtmlTagString() . ">';
			}

			else item = '';
		";
	}
}
