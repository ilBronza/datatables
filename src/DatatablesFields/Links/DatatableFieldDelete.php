<?php

namespace IlBronza\Datatables\DatatablesFields\Links;

class DatatableFieldDelete extends DatatableFieldLink
{
    public $icon = 'trash';
    public $textParameter = false;

	public function transformValue($value)
	{
		if(! $this->textParameter)
			return $value->getDeleteUrl();

		return [
			$value->getDeleteUrl(),
			$value->{$this->textParameter}
		];
	}

	public function getCustomColumnDefSingleResult()
	{
		return "
			if(item)
				item = '<a data-method=\"DELETE\" class=\"ib-ajax-button\" href=\"javascript:void(0)\" data-url=\"' + " . $this->getLinkUrlString() . " + '\">" . $this->getIconHtml() . "' + " . $this->getLinkTextString() . " + '</a>';

			else item = ''";
	}

}
