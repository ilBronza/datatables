<?php

namespace IlBronza\Datatables\DatatablesFields\Relations;

use IlBronza\Datatables\DatatablesFields\DatatableField;
use IlBronza\Datatables\Traits\DatatablesFields\DatatablesFieldsRelationsTrait;

class DatatableFieldBelongsTo extends DatatableField
{
    use DatatablesFieldsRelationsTrait;

    public function transformValue($value)
    {
        if($value)
            return [
                'id' => $value->getKey(),
                'name' => $value->getNameForDisplayRelation()
            ];            
    }

    public function getCustomColumnDef()
    {
        $fieldIndex = $this->getIndex();

        return "
        {
            targets: [{$fieldIndex}],
            render: function ( data, type, row, meta )
            {
                if((data === null)||(typeof data === null)||(typeof data.id === null)||(typeof data.name === null))
                    return null;

                if(type == 'display')
                {
                    let urlRelation = '" . $this->getRelationModelSprintFShowRoute() . "';

                    return '<a href=\"' + urlRelation.replace('%s', data.id) + '\">' + data.name + '</a><br />';
                }

                if(type == 'sort')
                    return data.name;

                return data.name;
            }
        }";
    }
}

