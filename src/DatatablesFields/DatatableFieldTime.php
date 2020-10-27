<?php

namespace IlBronza\Datatables\DatatablesFields;

use IlBronza\Datatables\DatatablesFields\Dates\DatatableFieldCarbon;

class DatatableFieldTime extends DatatableFieldCarbon
{
    public function getCustomColumnDef()
    {
        $fieldIndex = $this->getIndex();

        return "
        {
            targets: [{$fieldIndex}],
            render: function ( data, type, row, meta )
            {
                if(type == 'display')
                {
                    let date = moment.unix(data);

                    return date;

                    if(date.isValid())
                        return date.format(\"hh:mm\");

                    return data;
                }

                return data;
            }
        }
        ";
    }
}

