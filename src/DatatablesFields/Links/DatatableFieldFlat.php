<?php

namespace IlBronza\Datatables\DatatablesFields\Links;

class DatatableFieldFlat extends DatatableFieldLink
{
	public $defaultWidth = 'auto';

	public function isSortable()
	{
		return $this->sortable;
	}

	public function transformValue($value)
	{
		return [
			$value,
			$value
		];
	}

	public function getLinkTextString()
	{
		return "((item[1])? item[1] : '' )";
	}

	public function getLinkUrlString()
	{
		return "item[0]";
	}

	public function getIconHtml()
	{
		if($this->avoidIcon ?? false)
			return ;

		if($this->icon ?? false)
			return "<span uk-icon=\"{$this->icon}\"></span>";

		return ;
	}
}
