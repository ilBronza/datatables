<?php

namespace IlBronza\Datatables\DatatablesFields\Dates;

class DatatableFieldSince extends DatatableFieldCarbon
{
    public function getCustomColumnDefSingleResult()
    {
        return "

            if(item)
            {
                let date = moment.unix(item);

                if(date.isValid())
                    item = date.fromNow(true);
            }

            else item = ''";
    }

    public function getCustomColumnDefSingleSearchResult()
    {
        return "
            if(item)
            {
                let date = moment.unix(item);

                if(date.isValid())
                    item = date.fromNow(true);
            }

            else item = ''";
    }
}

