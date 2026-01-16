<?php

namespace IlBronza\Datatables\DatatablesFields\Html;

use IlBronza\Datatables\DatatablesFields\DatatableField;

class DatatableFieldTag extends DatatableField
{
	public $tag;
	public $tagClasses;

	public bool $valueAsTagClass = false;

	public function getValueAsTagClassString() : string
	{
		if(! $this->valueAsTagClass)
			return '';

		return "' + item.toLowerCase().normalize('NFD').replace(/[\u0300-\u036f]/g, '').replace(/[^a-z0-9\s-]/g, '').trim().replace(/\s+/g, '-').replace(/-+/g, '-') + '  ";
	}

	public function getCustomColumnDefSingleResult()
	{
		return "

            if(item)
				item = '<{$this->tag} class=\"" . $this->getValueAsTagClassString() . $this->tagClasses . "\">' + item + '</$this->tag>';

            else item = '';
		";
	}
}