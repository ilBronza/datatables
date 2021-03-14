<?php

namespace IlBronza\Datatables\DatatablesFields\Dates;

class DatatableFieldDate extends DatatableFieldCarbon
{
    public function getCustomColumnDefSingleResult()
    {
        return "
            if(item)
            {
                let date = moment.unix(item);

                if(date.isValid())
                    return date.format('" . $this->getDateFormat() . "');
            }

            else item = ''";
    }

    // public function getCustomColumnDef()
    // {
    //     $fieldIndex = $this->getIndex();

    //     return "
    //     {
    //         targets: [{$fieldIndex}],
    //         render: function ( data, type, row, meta )
    //         {
    //             if(type == 'display')
    //             {
    //                 let date = moment.unix(data);

    //                 if(date.isValid())
    //                     return date.format('" . $this->getDateFormat() . "');

    //                 return data;
    //             }

    //             return data;
    //         }
    //     }
    //     ";
    // }
}

