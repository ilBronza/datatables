<?php

namespace IlBronza\Datatables\DatatablesFields\Links;

use IlBronza\Datatables\DatatablesFields\DatatableField;
use IlBronza\Datatables\DatatablesFields\FieldTypesTraits\IconTextContentTrait;

class DatatableFieldLink extends DatatableField
{
	use IconTextContentTrait;

	public $icon = false;
	public $textParameter = false;
	public $staticText;
	public $textMethod = false;
	public $defaultWidth = '45px';
	public $showNull = false;
	public $dataAttributes = [];
	public $htmlTag = 'a';
	// public $filterType = 'none';

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

	public function transformValue($value)
	{
		if(! $this->textParameter)
		{
			if(! isset($this->variable))
			{
				if(! $this->textMethod ?? false)
					return $value->{$this->function}();

				return [
					$value->{$this->function}(),
					$value->{$this->textMethod}()
				];
			}

			$variableValue = $this->table->getVariable($this->variable);

			if(! $this->textMethod ?? false)
				return $value->{$this->function}($variableValue);

			return [
				$value->{$this->function}($variableValue),
				$value->{$this->textMethod}()
			];
		}

		try
		{
			if(! isset($this->variable))
				return [
					$value->{$this->function}(),
					$value->{$this->textParameter}
				];			
		}
		catch(\Throwable $e)
		{
			return [
				$e->getMessage(),
				$e->getMessage()
			];
		}

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
		if($this->hasText())
			return "item[0]";
		// if($this->textParameter)
		// 	return "item[0]";

		// if($this->staticText)
		// 	return "item[0]";

		// if($this->textMethod)
		// 	return "item[0]";

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
