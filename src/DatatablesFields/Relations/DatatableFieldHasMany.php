<?php

namespace IlBronza\Datatables\DatatablesFields\Relations;

use IlBronza\Datatables\DatatablesFields\Iterators\DatatableFieldIterator;
use IlBronza\Datatables\Traits\DatatablesFields\DatatablesFieldsRelationsTrait;

class DatatableFieldHasMany extends DatatableFieldIterator
{
    use DatatablesFieldsRelationsTrait;

    public $attributeGetterMethod;

    /**
     * set to false in array parameter to use normal routes models.action
     * instead of complex parents.models.action
     **/
    public $isDependentRelation = true;

    public function _transformValue($value)
    {
        if($attributeGetterMethod = $this->attributeGetterMethod)
            return [
                'id' => $value->getKey(),
                'name' => $value->{$attributeGetterMethod}()
            ];

        return [
            'id' => $value->getKey(),
            'name' => $value->getNameForDisplayRelation()
        ];
    }

    public function getDisplayColumnDef()
    {
        if(isset($this->table->modelClass))
            return "

                let result = '';
                let urlRelation = '" . $this->getRelationModelSprintFShowRoute() . "';

                urlRelation = urlRelation.replace('%f', data.father);

                Object.keys(elements).forEach(key => {

                    result = result + '<a href=\"' + urlRelation.replace('%s', elements[key].id) + '\">' + elements[key].name + '</a><br />';
                });
            ";

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

