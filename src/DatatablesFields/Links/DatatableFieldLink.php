<?php

namespace IlBronza\Datatables\DatatablesFields\Links;

use IlBronza\Datatables\DatatablesFields\DatatableField;
use IlBronza\Datatables\DatatablesFields\FieldTypesTraits\IconTextContentTrait;

class DatatableFieldLink extends DatatableField
{
	use IconTextContentTrait;

	public bool|string $lightbox = false;
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

		return $this->width;
    }

    public function isSortable()
    {
    	if(! $this->sortable)
    		return false;

    	if(! $this->textParameter)
    		return false;

    	return $this->sortable;
    }

	public function getValueGetterMethodName()
	{
		if($this->function)
			return $this->function;

		if($this->method)
			return $this->method;

		throw new \Exception('No method or function defined for DatatableFieldLink ' . $this->getName());
	}

	public function transformValue($value)
	{
		if(! $this->textParameter)
		{
			if(! isset($this->variable))
			{
				if(! $this->textMethod ?? false)
					return $value->{$this->getValueGetterMethodName()}();

				return [
					$value->{$this->getValueGetterMethodName()}(),
					$value->{$this->textMethod}()
				];
			}

			$variableValue = $this->table->getVariable($this->variable);

			if(! $this->textMethod ?? false)
				return $value->{$this->getValueGetterMethodName()}($variableValue);

			return [
				$value->{$this->getValueGetterMethodName()}($variableValue),
				$value->{$this->textMethod}()
			];
		}

		try
		{
			if(! isset($this->variable))
				return [
					$value->{$this->getValueGetterMethodName()}(),
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
				$value->{$this->getValueGetterMethodName()}(),
				$value->{$this->getValueGetterMethodName()}($variableValue)
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

	public function getLightboxOpeningString() : ?string
	{
		if ($this->lightbox)
			return '<div uk-lightbox>';

		return null;
	}

	public function getLightboxDataTypeString() : ?string
	{
		if ($this->lightbox)
			return " data-type=\"iframe\" ";

		return null;
	}

	public function getLightboxClosingString() : ?string
	{
		if ($this->lightbox)
			return '<div uk-lightbox>';

		return null;
	}

	public function getCustomColumnDefSingleResult()
	{
		$itemString = $this->hasText()? 'item[0]' : 'item';

		return "

			if(" . $itemString . ")
			{
				item = '" . $this->getLightboxOpeningString() . "<" . $this->getHtmlTagString() . $this->getParentDataIndexString() . $this->getHtmlDataAttributesString() . $this->getLightboxDataTypeString() .
			$this->getHtmlClassesAttributeString() .	" " . $this->getTargetHtml() .	" href=\"' + " . $this->getLinkUrlString() . " + '\">" . $this->getPrefix() . "" . $this->getIconHtml() . " ' + " .
			$this->getLinkTextString() . " + '" . $this->getSuffix() . "</" . $this->getHtmlTagString() . ">" . $this->getLightboxClosingString() . "';
			}

			else item = '';
		";
	}
}
