<?php

namespace ilBronza\Datatables\DatatablesFields\Dates;

class DatatableFieldDate extends DatatableFieldCarbon
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

                    if(date.isValid())
                        return date.format(\"D/MM/YYYY\");

                    return data;
                }

                return data;
            },
            createdCell: function (td, cellData, rowData, row, col)
            {
                if ( cellData < 4602692420 )
                {
                    $(td).css('color', '#ff0912');
                }
            }
        }
        ";
    }
}

