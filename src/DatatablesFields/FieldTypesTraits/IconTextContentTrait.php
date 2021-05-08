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

		if(! ($this->textParameter ?? false))
			return "<span uk-icon=\"link\"></span>";

		return ;
	}

	public function getLinkTextString()
	{
		if(! ($this->textParameter ?? false))
			return "''";

		return "item[1]";
	}
}