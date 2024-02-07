<?php

namespace IlBronza\Datatables\DatatablesFields\FieldTypesTraits;


trait IconTextContentTrait
{
	public function getIconHtml()
	{
		if($this->avoidIcon ?? false)
			return ;

		if($this->icon ?? false)
			return "<span uk-icon=\"{$this->icon}\"></span>";

		if($this->faIcon ?? false)
			return "<i class=\"fa-solid fa-{$this->faIcon}\"></i>";

		// if(! ($this->textParameter ?? false))
		// 	return "<span uk-icon=\"link\"></span>";

		return ;
	}

	public function hasText()
	{
		if($this->textParameter ?? false)
			return true;

		if($this->staticText ?? false)
			return true;

		if($this->textMethod ?? false)
			return true;

		return false;
	}

	public function getLinkTextString()
	{
		if($this->hasText())
		{
			if($this->showNull)
				return "item[1]";

			return "((item[1])? item[1] : '' )";
		}

		return "''";
	}
}