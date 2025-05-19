<?php

namespace IlBronza\Datatables\DatatablesFields\Relations;

use IlBronza\Datatables\DatatablesFields\Iterators\DatatableFieldIterator;
use IlBronza\Datatables\Traits\DatatablesFields\DatatablesFieldsRelationsTrait;

use function explode;

class DatatableFieldBelongsToMany extends DatatableFieldIterator
{
    public $pivot = false;

    use DatatablesFieldsRelationsTrait;

	public $function = 'getNameForDisplayRelation';

	public function getRelationParameterGetterName() : string
	{
		return $this->function;
	}

    public function _transformValue($value)
    {
		$methodName = $this->getRelationParameterGetterName();

        if(! $this->pivot)
            return [
                'id' => $value->getKey(),
                'name' => $value->{$methodName}(),
            ];

        return [
            'pivotId' => $value->pivot->id,
            'id' => $value->getKey(),
            'name' => $value->{$methodName}(),
            'active' => ! ($value->pivot->deleted_at ?? false)
        ];
    }

    public function getDisplayColumnDef()
    {
        if(! $this->pivot)
            return "
                let result = '';
                let urlRelation = '" . $this->getRelationModelSprintFShowRoute() . "';

                Object.keys(elements).forEach(key => {

                    let _result = '<a href=\"' + urlRelation.replace('%s', elements[key].id) + '\">' + elements[key].name + '</a>';

                    _result = '<nobr>' + _result + '</nobr>" . $this->getSeparator() . "';

                    result = result + _result;
                });
            ";

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

