<?php

namespace IlBronza\Datatables\DatatablesFields\Relations;

use IlBronza\Datatables\DatatablesFields\Iterators\DatatableFieldIterator;
use IlBronza\Datatables\Traits\DatatablesFields\DatatablesFieldsRelationsTrait;

class DatatableFieldBelongsToManyFlat extends DatatableFieldBelongsToMany
{
    public function _transformValue($value)
    {
		$methodName = $this->getRelationParameterGetterName();

        return [
            'name' => $value->{$methodName}(),
        ];

    }

    public function getDisplayColumnDef()
    {
        return "
            let result = '';

            Object.keys(elements).forEach(key => {

                let mutedString = (elements[key].active) ? '' : 'class=\"uk-text-muted\"';

                let _result = elements[key].name;

                _result = '" . $this->getSeparator() . "';

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

