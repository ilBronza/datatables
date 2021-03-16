<?php

namespace IlBronza\Datatables\DatatablesFields\Dates;

class DatatableFieldDate extends DatatableFieldCarbon
{
    public $dateFormat = "D/MM/YYYY";

    public function getCustomColumnDefSingleResult()
    {
        return "

            if(item)
            {
                let date = moment.unix(item);

                if(date.isValid())
                    item = date.format('" . $this->getDateFormat() . "');
            }

            else item = ''";
    }
}

