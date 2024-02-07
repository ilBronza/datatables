<?php

namespace IlBronza\Datatables\Traits\DatatablesFields;

use IlBronza\Datatables\DatatablesFieldOperation;
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

    public function getFieldOperationIcon($operation)
    {
        if(is_array($operation))
            return $operation['icon'] ?? 'link';

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

        foreach($this->getFieldOperations() as $index => $fieldOperation)
            $result[$index] = $this->getFieldOperationIcon($fieldOperation);

        return $result;
    }

    public function hasFieldOperations()
    {
        return count($this->getFieldOperations());
    }

    public function getFieldOperations() : array
    {
        $result = [];

        foreach($this->fieldOperations ?? [] as $index => $value)
            if((is_int($index))&&(is_string($value)))
                $result[$value] = DatatablesFieldOperation::createFromParameters($value, $value);
            else
                $result[$index] = DatatablesFieldOperation::createFromParameters($value, $index);

        return $result;
    }

    public function getFilteredTable()
    {
        return $this->filteredTable ?? false;
    }
}