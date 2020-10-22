<?php

namespace IlBronza\Datatables\DatatablesFields;

class DatatableFieldSee extends DatatableField
{
    public function transformValue($value)
    {
        return $value->getShowUrl();
    }

    public function getCustomColumnDef()
    {
		return "
		{
			targets: [" . $this->getIndex() . "],
			render: function ( data, type, row, meta )
			{
				if(type == 'display')
					return '<a uk-icon=\"link\" href=\"' + data + '\" ></a>';

				return data;
			}
		}
		";
	}
}
