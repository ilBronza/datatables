<?php

namespace IlBronza\Datatables\DatatablesFields\Links;

use IlBronza\Datatables\DatatablesFields\DatatableField;

class DatatableFieldLink extends DatatableField
{
	public $icon = 'link';

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
			return "<span uk-icon=\"" . $this->icon . "\"></span>";

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

	public function getCustomColumnDefSingleResult()
	{
		// if(! $this->textParameter)
		// 	return "
		// 	if(item)
		// 		item = '<a " . $this->getTargetHtml() . " href=\"' + item + '\">" . $this->getIconHtml() . "</a>';

		// 	else item = ''";

		// return "
		// 	if(item)
		// 		item = '<a " . $this->getTargetHtml() . "  href=\"' + item[0] + '\">" . $this->getIconHtml() . "' + item[1] + '</a>';

		// 	else item = ''";

		return "
			if(item)
				item = '<a " . $this->getTargetHtml() . " href=\"' + " . $this->getLinkUrlString() . " + '\">" . $this->getIconHtml() . "' + " . $this->getLinkTextString() . " + '</a>';

			else item = ''";
	}

    public function getCustomColumnDef()
    {
		return "
		{
			targets: [" . $this->getIndex() . "],
			render: function ( data, type, row, meta )
			{
				if(type == 'display')
					return " . $this->getLinkColumnDefResult() . ";

				return data;
			}
		}
		";
	}

	public function getIconString()
	{
		return "uk-icon=\"" . $this->icon . "\" ";
	}

	public function getLinkColumnDefResult()
	{
		return "'<a " . $this->getIconString() . " href=\"' + data[0] + '\" >' + data[1] + '</a>'";
	}
}
