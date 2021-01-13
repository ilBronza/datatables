<?php

namespace IlBronza\Datatables\DatatablesFields\Links;

use IlBronza\Datatables\DatatablesFields\Links\DatatableFieldIconLink;

class DatatableFieldEdit extends DatatableFieldIconLink
{
	public $icon = 'file-edit';

	public function transformValue($value)
	{
		if(! $this->textParameter)
			return $value->getEditUrl();

		return [
			$value->getEditUrl(),
			$value->{$this->textParameter}
		];
	}

    public function getCustomColumnDef()
    {
		if(! $this->textParameter)
			return $this->_getCustomColumnDef();

        $fieldIndex = $this->getIndex();

        return "
        {
            targets: [" . $this->getIndex() . "],
            render: function ( data, type, row, meta )
            {
                if(type == 'display')
                    return '<a href=\"' + data[0] + '\" >" . $this->getIconHtml() . "' + data[1] + '</a>';

                return data;
            }
        }
        ";
    }
}
