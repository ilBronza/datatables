<?php

namespace IlBronza\Datatables\Traits\DatatablesFields;

use IlBronza\Datatables\DatatablesFieldOperation;
use Illuminate\Support\Str;
use InvalidArgumentException;

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
        {
            if((is_int($index))&&(is_string($value)))
                $operation = DatatablesFieldOperation::createFromParameters($value, $value);
            else
                $operation = DatatablesFieldOperation::createFromParameters($value, $index);

            if($operation->getType() === 'delete')
            {
                if(! $operation->getLink())
                    $operation->setLink($this->getDeleteFieldOperationLink());

                if(! $operation->getFaIcon() && ! $operation->icon)
                    $operation->setFaIcon('trash');
            }

            $result[$operation->getName()] = $operation;
        }

        return $result;
    }

    protected function getDeleteFieldOperationLink() : string
    {
        if(! $this->modelClass)
            throw new InvalidArgumentException('The delete field operation requires modelClass or an explicit link.');

        if(method_exists($this, 'getFieldPlaceholderModel'))
            $model = $this->getFieldPlaceholderModel();
        else
        {
            $modelClass = $this->modelClass;
            $model = $modelClass::make();
            $model->{$model->getKeyName()} = config('datatables.replace_model_id_string');
        }

        if(! method_exists($model, 'getDeleteUrl'))
            throw new InvalidArgumentException('The delete field operation model must provide getDeleteUrl(), or the operation must specify a link.');

        return $model->getDeleteUrl();
    }

    /** Editor actions share the existing update endpoints and AJAX response handling. */
    public function getEditorFieldOperations() : array
    {
        $operations = [];

        foreach ($this->fieldOperations as $name => $parameters)
        {
            if (! is_array($parameters) || ! in_array($parameters['type'] ?? null, ['editor.toggle', 'editor.ajax'], true))
                continue;

            if (isset($parameters['confirmMessage']))
                $parameters['confirmMessage'] = __($parameters['confirmMessage']);

            if (isset($parameters['fetcher']['urlMethod']))
            {
                $model = method_exists($this, 'getFieldPlaceholderModel')
                    ? $this->getFieldPlaceholderModel()
                    : $this->getPlaceholderElement();
                $parameters['fetcher']['url'] = $model->{$parameters['fetcher']['urlMethod']}();
            }

            $operations[$name] = $parameters;
        }

        return $operations;
    }

    public function getFieldOperationValues(object $model) : array
    {
        $values = [];

        foreach ($this->fieldOperations as $name => $parameters)
        {
            if (! is_array($parameters) || ($parameters['type'] ?? null) !== 'editor.toggle')
                continue;

            $values[$name] = isset($parameters['editorValueFunction'])
                ? $model->{$parameters['editorValueFunction']}()
                : $model->{$parameters['editorProperty']};
        }

        return $values;
    }

    public function getFilteredTable()
    {
        return $this->filteredTable ?? false;
    }
}
