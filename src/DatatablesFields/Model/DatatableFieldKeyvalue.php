<?php

namespace IlBronza\Datatables\DatatablesFields\Model;

use IlBronza\Datatables\DatatablesFields\Keyvalue\DatatableFieldKeyvalue as BaseDatatableFieldKeyvalue;

/** A key/label cell backed by an already loaded model, including iterator children. */
class DatatableFieldKeyvalue extends BaseDatatableFieldKeyvalue
{
    public ?string $labelProperty = null;

    public function transformValue($value)
    {
        if (! $value)
            return [null, null];

        $result = [$value->getKey(), $value->{$this->labelProperty}];

        if ($operations = $this->getFieldOperationValues($value))
            $result[2] = $operations;

        return $result;
    }

    public function getCustomColumnDefSingleResult()
    {
        return $this->getDisplayCustomColumnDefSingleResult() . "\nelse item = '';";
    }

    public function _getCustomColumnDefSingleSearchResult()
    {
        return 'item = item[1];';
    }
}
