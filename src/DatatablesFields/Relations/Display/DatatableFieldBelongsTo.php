<?php

namespace IlBronza\Datatables\DatatablesFields\Relations\Display;

use IlBronza\Datatables\DatatablesFields\DatatableField;
use IlBronza\Datatables\Traits\DatatablesFields\DatatablesFieldsRelationsTrait;

class DatatableFieldBelongsTo extends DatatableField
{
    public function transformValue($value)
    {
        if(! $value)
            return ;
        
        return $value->getNameForDisplayRelation();
    }
}

