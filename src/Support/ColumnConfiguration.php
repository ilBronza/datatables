<?php

namespace IlBronza\Datatables\Support;

use IlBronza\Datatables\DatatablesFields\DatatableFieldSelectRowCheckbox;

class ColumnConfiguration
{
    public static function field($field) : array
    {
        $valueField = $field;
        while (isset($valueField->child)) $valueField = $valueField->child;

        return [
            'name' => $field->getFieldName(),
            'column' => $field->getCamelName(),
            'index' => $field->getIndex(),
            'width' => $field->getUnstyledWidth(),
            // The built-in select-all control and Select extension use the first column.
            'locked' => $field instanceof DatatableFieldSelectRowCheckbox,
            'states' => method_exists($valueField, 'getBooleanStyleDefinitions')
                ? $valueField->getBooleanStyleDefinitions() : [],
        ];
    }
}
