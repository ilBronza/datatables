<?php

namespace IlBronza\Datatables\DatatablesFields\Dates;

class DatatableFieldDatetime extends DatatableFieldCarbon
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
                        return date.format(\"D/MM/YYYY h:mm\");

                    return data;
                }

                return data;
            }
        }
        ";
    }
}

