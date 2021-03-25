<?php

namespace IlBronza\Datatables\Traits\DatatablesFields;

use Illuminate\Support\Str;

trait DatatablesFieldsOperationsTrait
{
    public function manageFieldOperations(array $parameters = [])
    {
        if(isset($parameters['filteredTable']))
            $this->fieldOperations[] = 'filteredTable';

        if(! $fieldOperations = $this->hasFieldOperations())
            return ;

        $this->addHeaderHtmlClass('fieldOperations');
    }

    public function getFieldOperationIcon(string $operation)
    {
        if($operation == 'checkVisible')
            return 'check';

        if($operation == 'ban')
            return 'ban';

        if($operation == 'search')
            return 'search';

        if($operation == 'close')
            return 'close';

        if($operation == 'checkAll')
            return 'check';

        if($operation == 'filteredTable')
            return 'table';

        mori('dichiara operazione ' . $operation);
    }

    public function getFieldOperationsIcons()
    {
        $result = [];

        foreach($this->fieldOperations as $fieldOperation)
            $result[$fieldOperation] = $this->getFieldOperationIcon($fieldOperation);

        return $result;
    }

    public function hasFieldOperations()
    {
        return count($this->getFieldOperations());
    }

    public function getFieldOperations() : array
    {
        return $this->fieldOperations;
    }

    public function getFilteredTable()
    {
        return $this->filteredTable ?? false;
    }
}