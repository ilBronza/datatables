<?php

namespace IlBronza\Datatables\Traits\DatatablesFields;

trait DatatablesFieldsColumnDefsTrait
{
    public function getSuffix()
    {
        return $this->suffix ?? false;
    }

    private function setClassnameColumnDef()
    {
        //add className to field columnDefs
        if(! isset($this->columnDefs['className']))
            $this->columnDefs['className'] = $this->getHtmlClass();        
    }

    public function getColumnDefs()
    {
        $this->setClassnameColumnDef();

        return $this->columnDefs;
    }

    /**
     * set field own columnDefs
     */
    private function setColumnDefs()
    {
        $this->columnDefs = [];

        //parse through available columnDefs parameters and id set, store it
        foreach($this->availableColumnDefs as $availableColumnDef)
            $this->setColumnDef($availableColumnDef);
    }

    /**
     * if exists in object, add columnDef to field
     */
    public function setColumnDef(string $columnDef)
    {
        if(($value = $this->$columnDef?? null) !== null)
            $this->addColumnDef($columnDef, $value);
    }

    /**
     * add columnDef to field
     *
     * @param string $name
     * @param mixed $value
     */
    public function addColumnDef(string $columnDef, $value)
    {
        $this->columnDefs[$columnDef] = $value;
    }

    public function getCustomColumnDef()
    {
        // if(! $this->getEndingResultOptions())
        //     return ;

        return "
        {
            targets: [" . $this->getIndex() . "],
            render: function ( item, type, row, meta )
            {
                if(type == 'display')
                {
                    " . $this->getColumnDefSingleResult() . "
                    " . $this->getEndingResultOptions() . "

                    return item;
                }

                return item;
            }
        }
        ";
    }

    public function getEndingResultOptions()
    {
        $result = [];

        if($this->getSuffix())
            $result[] = "
            if(item)
                item = item + '" . $this->getSuffix() . "';
        ";

        return implode(" ", $result);
    }

    public function getColumnDefSingleResult()
    {
        return $this->getEndingResultOptions();
    }
}
