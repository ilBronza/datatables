<?php

namespace IlBronza\Datatables\DatatablesFields\Dates;

class DatatableFieldDate extends DatatableFieldCarbon
{
	public ?string $textAlign = 'right';
    public $dateFormat = "DD/MM/YYYY";
    public $inputFieldDefaultFormat = "YYYY-MM-DD";
	public $defaultWidth = '5em';
	public $width = '9em';

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

    public function getCustomColumnDefSingleResultExport()
    {
        return "
            if(item)
            {
                let date = moment.unix(item);

                if(date.isValid())
                    item = date.format('YYYY-MM-DD');
            }

            else item = ''";
    }


    
}

