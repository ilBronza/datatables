<?php

namespace IlBronza\Datatables\DatatablesFields\FieldTypesTraits;


trait HtmlClassesAttributesTrait
{
	public function getHtmlClassesAttributeString()
	{
		if (! $classes = $this->getHtmlClassesString())
			return ;

		return " class=\"{$classes}\" ";
	}	
}