<?php

namespace IlBronza\Datatables\DatatablesFields\Html;

use IlBronza\Datatables\DatatablesFields\DatatableField;

class DatatableFieldTag extends DatatableField
{
	public $tag;
	public $tagClasses;

	public function getCustomColumnDefSingleResult()
	{
		return "

            if(item)
				item = '<{$this->tag} class=\"" . $this->tagClasses . "\">' + item + '</$this->tag>';

            else item = '';
		";
	}
}