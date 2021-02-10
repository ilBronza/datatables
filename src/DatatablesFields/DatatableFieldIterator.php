<?php

namespace IlBronza\Datatables\DatatablesFields;

use IlBronza\Datatables\DatatablesFields\DatatableField;

class DatatableFieldIterator extends DatatableField
{
    public $separator = '<br />';

    public function getSeparator()
    {
        return $this->separator;
    }

    public function transformValue($value)
    {
        $result = [];

        foreach($value as $_value)
            $result[] = $this->_transformValue($_value);

        return [
            'separator' => $this->separator,
            'elements' => $result
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
        }
        ";
    }
}

