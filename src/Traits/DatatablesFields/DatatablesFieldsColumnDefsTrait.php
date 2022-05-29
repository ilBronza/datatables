<?php

namespace IlBronza\Datatables\Traits\DatatablesFields;

trait DatatablesFieldsColumnDefsTrait
{
    public function getSuffix()
    {
        return $this->suffix ?? false;
    }

    public function getPrefix()
    {
        return $this->prefix ?? false;
    }

    private function setClassnameColumnDef()
    {
        //add className to field columnDefs
        if(! isset($this->columnDefs['className']))
            $this->columnDefs['className'] = $this->getCamelName();        
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
        foreach($this->availableColumnDefs as $ilBronzaDefinition => $datatablesDefinition)
        {
            $this->setColumnDef($ilBronzaDefinition, $datatablesDefinition);
        }
    }

    /**
     * if exists in object, add columnDef to field
     */
    public function setColumnDef(string $ilBronzaDefinition, string $datatablesDefinition)
    {
        if(! is_null($value = $this->getDatatableUserDataParameter($datatablesDefinition)))
            $this->addColumnDef($datatablesDefinition, $value);

        elseif(($value = $this->$ilBronzaDefinition?? null) !== null)
            $this->addColumnDef($datatablesDefinition, $value);
    }

    /**
     * add columnDef to field
     *
     * @param string $name
     * @param mixed $value
     */
    public function addColumnDef(string $columnDef, $value)
    {
        if($columnDef == 'datatableType')
            $columnDef = 'type';

        $this->columnDefs[$columnDef] = $value;
    }

    public function getCustomColumnDefSingleResultEditor()
    {
        return $this->getCustomColumnDefSingleResult();
    }

    public function getItemString()
    {
        if($this->requiresKey())
            return 'item[1]';

        return 'item';
    }

    public function getCustomColumnDefSingleResult()
    {
        if($this->getParentDataIndexString()||($this->getHtmlDataAttributesString())||$this->getHtmlClassesAttributeString())
                return "
                if(item === null)
                    item = '';
                else 
                {
                    //manzissimo;
                    item = '<span " . $this->getParentDataIndexString() . $this->getHtmlDataAttributesString() . $this->getHtmlClassesAttributeString() . " >' + " . $this->getItemString() . " + '</span>';
                }
            ";

        return "
            if(item === null)
                item = '';
        ";

        // $result .= $this->getEndingResultOptions();
    }

    public function getCustomColumnDefSingleSearchResult()
    {
        return "
            return item;
        ";
    }

    public function getCustomColumnDefSingleSortResult()
    {
        return "
            return item;
        ";
    }

    public function getCustomColumnDef()
    {
        // if(! $this->getEndingResultOptions())
        //     return ;

        return "
        {
            //" . $this->getName() . "
            targets: [" . $this->getIndex() . "],
            render: function ( item, type, row, meta )
            {
                if(type == 'display')
                {
                    " . $this->getCustomColumnDefSingleResult() . "
                    " . $this->getEndingResultOptions() . "

                    return item;
                }

                if(type == 'export')
                {
                    " . $this->getCustomColumnDefSingleResultEditor() . "
                    " . $this->getEndingResultOptionsEditor() . "

                    return item;
                }

                if(type == 'filter')
                {
                    " . $this->getCustomColumnDefSingleSearchResult() . "

                    return item;
                }

                if(type == 'sort')
                {
                    " . $this->getCustomColumnDefSingleSortResult() . "

                    return item;
                }

                return item;
            }
        }";
    }

    public function getEndingResultOptionsEditor()
    {
        return $this->getEndingResultOptions();
    }

    public function getEndingResultOptions()
    {
        $result = [];

        if($this->getSuffix())
            $result[] = "
            if(item)
                item = item + '" . $this->getSuffix() . "';
        ";

        if($this->getPrefix())
            $result[] = "
            if(item)
                item = '" . $this->getPrefix() . "' + item;
        ";

        return implode(" ", $result);
    }

    public function getColumnDefSingleResult()
    {
        return $this->getEndingResultOptions();
    }

    public function getValueAsRowClassScript()
    {
        if($this->valueAsRowClass)

            return "
        //" . $this->name . "
        window.valueAsClass = data[" . $this->getIndex() . "];

        if(typeof window.valueAsClass !== 'undefined')
        {
            if(typeof window.valueAsClass !== 'string')
                window.valueAsClass = JSON.stringify(window.valueAsClass);

            $(row).addClass('" . $this->getValueAsRowClassPrefix() . "' + window.valueAsClass.replace(/[^a-zA-Z ]/g, ' '));
        }

        ";
    }

    public function getCompiledAsRowClassConditionScript()
    {
        return "window.compiledAsClass = (data[" . $this->getIndex() . "] !== 'undefined');";
    }

    public function getCompiledAsRowClassScript()
    {
        if($this->compiledAsRowClass)
            return "
        //" . $this->name . "

        " . $this->getCompiledAsRowClassConditionScript() . "
        
        if(window.compiledAsClass)
            $(row).addClass('" . $this->getCompiledAsRowClassPrefix() . "compiled');
        else
            $(row).addClass('" . $this->getCompiledAsRowClassPrefix() . "notcompiled');
        ";
    }

    public function getCreatedRowScripts()
    {
        $result = [];

        if($script = $this->getValueAsRowClassScript())
            $result[] = $script;

        if($script = $this->getCompiledAsRowClassScript())
            $result[] = $script;

        return implode(" ", $result);
    }
}
