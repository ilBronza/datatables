<?php

namespace IlBronza\Datatables\DatatablesFields\Iterators;

class DatatableFieldList extends MultipleDatatableField
{
    public $function;

    public function getFunction() : ? string
    {
        return $this->function;
    }

    public function transformValue($value)
    {
        $result = [];

        if($function = $this->getFunction())
            $value = $value->{$function}();

        if(! $value)
            return ;

        foreach($value as $_value)
            $result[] = $this->_transformValue($_value);

        return [
            'separator' => $this->separator,
            'elements' => $result,
        ];
    }

    public function _transformValue($value)
    {
        return $value;
    }

    public function tooltipTransformResultCode()
    {
        if(! $this->hasTooltip())
            return ;

        return "
            result = '<div class=\"uk-text-truncate\">' + result + '</div>';
        ";
    }

    public function getDisplayColumnDef()
    {
        return "
            let result = '';

            Object.keys(elements).forEach(key => {
                result = result + elements[key] + '<br />';
            });
        ";
    }

    public function getFilterColumnDef()
    {
        return "
            let result = '';

            Object.keys(elements).forEach(key => {
                result = result + ' ' +  elements[key];
            });
        ";
    }

    public function getCustomColumnDef()
    {
        $fieldIndex = $this->getIndex();

        return "
        {
            targets: [{$fieldIndex}],
            render: function ( data, type, row, meta )
            {
                if((! data)||(typeof data.elements === 'undefined'))
                    return data;

                if(type == 'display')
                {
                    let elements = data.elements;

                    " . $this->getDisplayColumnDef() . "

                    " . $this->tooltipTransformResultCode() . "

                    return result;
                }

                if(type == 'sort')
                    return data.elements.length;

                if(type == 'filter')
                {
                    let elements = data.elements;

                    " . $this->getFilterColumnDef() . "

                    return result;
                }

                return data;
            }
        }";
    }
}

