<?php

namespace IlBronza\Datatables\DatatablesFields\Iterators;

class DatatableFieldIterator extends MultipleDatatableField
{
    public function transformValue($value)
    {
        $result = [];

        if(! $value)
            return ;

        foreach($value as $_value)
            $result[] = $this->_transformValue($_value);

        array_pop($this->elementValues);

        if($father = array_pop($this->elementValues))
            return [
                'separator' => $this->separator,
                'elements' => $result,
                'father' => $father->getKey()
            ];

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

