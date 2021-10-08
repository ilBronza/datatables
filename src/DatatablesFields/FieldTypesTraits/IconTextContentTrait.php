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
		if(($this->textParameter ?? false)||($this->textMethod ?? false))
		{
			if($this->showNull)
				return "item[1]";

			return "((item[1])? item[1] : '' )";
		}

		return "''";
	}
}