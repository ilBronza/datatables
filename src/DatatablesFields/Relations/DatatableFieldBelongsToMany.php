<?php

namespace IlBronza\Datatables\DatatablesFields\Relations;

use IlBronza\Datatables\DatatablesFields\Iterators\DatatableFieldIterator;
use IlBronza\Datatables\Traits\DatatablesFields\DatatablesFieldsRelationsTrait;

class DatatableFieldBelongsToMany extends DatatableFieldIterator
{
    use DatatablesFieldsRelationsTrait;

    public function _transformValue($value)
    {
        return [
            'pivotId' => $value->pivot->id,
            'id' => $value->getKey(),
            'name' => $value->getNameForDisplayRelation(),
            'active' => ! ($value->pivot->deleted_at ?? false)
        ];
    }

    public function getDisplayColumnDef()
    {
        return "
            let result = '';
            let urlRelation = '" . $this->getRelationModelSprintFShowRoute() . "';
            let urlPivot = '" . $this->getRelationPivotSprintFShowRoute() . "';

            Object.keys(elements).forEach(key => {

                let mutedString = (elements[key].active) ? '' : 'class=\"uk-text-muted\"';

                let _result = '<a href=\"' + urlPivot.replace('%s', elements[key].pivotId) + '\" uk-icon=\"cog\" ratio=\"0.8\"></a> ';

                _result = _result + '<a ' + mutedString + ' href=\"' + urlRelation.replace('%s', elements[key].id) + '\">' + elements[key].name + '</a>';

                _result = '<nobr>' + _result + '</nobr>" . $this->getSeparator() . "';

                result = result + _result;

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

