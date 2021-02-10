<?php

namespace IlBronza\Datatables\DatatablesFields\Relations;

use IlBronza\Datatables\DatatablesFields\DatatableFieldIterator;
use IlBronza\Datatables\Traits\DatatablesFields\DatatablesFieldsRelationsTrait;

class DatatableFieldHasMany extends DatatableFieldIterator
{
    use DatatablesFieldsRelationsTrait;

    public function _transformValue($value)
    {
        return [
            'id' => $value->getKey(),
            'name' => $value->getNameForDisplayRelation()
        ];
    }

    public function getDisplayColumnDef()
    {
        return "
            let result = '';
            let urlRelation = '" . $this->getRelationModelSprintFShowRoute() . "';

            Object.keys(elements).forEach(key => {

                result = result + '<a href=\"' + urlRelation.replace('%s', elements[key].id) + '\">' + elements[key].name + '</a><br />';
            });
        ";
    }

    public function getFilterColumnDef()
    {
        return "
            let result = '';

            Object.keys(elements).forEach(key => {
                result = result + elements[key].name;
            });
        ";
    }
}

