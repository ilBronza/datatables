<?php

namespace IlBronza\Datatables\DatatablesFields\Links;

use IlBronza\Datatables\DatatablesFields\DatatableField;

class DatatableFieldLink extends DatatableField
{
	public $icon = false;
	public $textParameter = false;
	public $defaultWidth = '25px';
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

	public function getIconHtml()
	{
		if($this->icon)
			return "<span uk-icon=\"{$this->icon}\"></span>";

		if(! $this->textParameter)
			return "<span uk-icon=\"link\"></span>";

		return ;
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

	public function getLinkTextString()
	{
		if(! $this->textParameter)
			return "''";

		return "item[1]";
	}

	public function getHtmlClassesAttribute()
	{
		if (! $classes = $this->getHtmlClassesString())
			return ;

		return " class=\"{$classes}\"";
	}

	public function getHtmlDataAttributesString()
	{
		$result = [];

		foreach($this->dataAttributes ?? [] as $data => $value)
			$result[] = "data-" . $data . "\"" . $value . "\"";

		return implode(" ", $result);
	}

	public function getCustomColumnDefSingleResult()
	{
		return "
			if(item)
				item = '<a " . $this->getHtmlDataAttributesString() . " " . $this->getHtmlClassesAttribute() . " " . $this->getTargetHtml() . " href=\"' + " . $this->getLinkUrlString() . " + '\">" . $this->getIconHtml() . "' + " . $this->getLinkTextString() . " + '</a>';

			else item = '';
		";
	}
}
