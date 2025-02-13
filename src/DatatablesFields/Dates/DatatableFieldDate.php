<?php

namespace IlBronza\Datatables\DatatablesFields\Dates;

class DatatableFieldDate extends DatatableFieldCarbon
{
    public $dateFormat = "D/MM/YYYY";
    public $inputFieldDefaultFormat = "YYYY-MM-DD";
	public $defaultWidth = '5em';
	public $width = '5em';

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

    public function getCustomColumnDefSingleSearchResult()
    {
        return "
            if(item)
            {
                let date = moment.unix(item);

                if(date.isValid())
                    item = date.format('" . $this->getInputFieldDefaultDateFormat() . "');
            }

            else item = ''";
    }
}

