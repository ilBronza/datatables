<?php

namespace IlBronza\Datatables\Traits;

use IlBronza\Datatables\DatatablesFields\DatatableField;
use Illuminate\Support\Collection;

trait DatatableSelectRowsTrait
{
    public function getFirstFieldsGroup()
    {
        return $this->fieldsGroups->first();
    }

    public function hasSelectRowCheckboxes()
    {
        return $this->selectRowCheckboxes ?? false;
    }

    public function setRowSelectCheckboxes()
    {
        $fieldsGroup = $this->createFieldsGroup('selectRow');

        $selectRowField = $this->addField('selectRow', [
			"type" => "selectRowCheckbox"
        ]);

        $fieldsGroup->addField('selectRow', $selectRowField);

        $primaryField = $this->addField('mySelfPrimary', [
            'type' => 'primary',
            'forcedStandardName' => 'mySelfPrimary',
        ]);
        $fieldsGroup->addField('mySelfPrimary', $primaryField);

        $this->selectRowCheckboxes = true;
    }

    public function fieldsGroupsRequiresSelectRowCheckboxes(array $fieldsGroups) : bool
    {
        foreach($fieldsGroups as $fieldsGroup)
            foreach($fieldsGroup['fields'] as $name => $parameters)
            {
                $field = $this->craeteField($name, $parameters);

                if($field->requiresSelectRowCheckboxes() || $field->isBulkEditable())
                    return true;
            }

        return false;
    }

    public function getBulkEditableFields() : Collection
    {
        return $this->getFields()->filter(
            fn (DatatableField $field) => $field->isBulkEditable()
        );
    }

    public function hasBulkEditableFields() : bool
    {
        return $this->getBulkEditableFields()->isNotEmpty();
    }

    public function getBulkEditableFieldsArray() : array
    {
        return $this->getBulkEditableFields()->map(function (DatatableField $field)
        {
            $fieldData = $field->getFieldSpecificData();

            return [
                'name' => $field->getFieldName(),
                'parameter' => $fieldData['field'] ?? $field->getFieldName(),
                'index' => $field->getIndex(),
                'label' => $field->getTranslatedName(),
            ];
        })->values()->all();
    }

    public function hasBulkEditButton() : bool
    {
        return $this->hasSelectRowCheckboxes() && $this->hasBulkEditableFields();
    }

    public function hasBulkToggleButton() : bool
    {
        return $this->hasBulkEditButton();
    }

    /** @deprecated use getBulkEditableFields() */
    public function getToggleableFields() : Collection
    {
        return $this->getBulkEditableFields();
    }

    /** @deprecated use hasBulkEditableFields() */
    public function hasToggleableFields() : bool
    {
        return $this->hasBulkEditableFields();
    }

    /** @deprecated use getBulkEditableFieldsArray() */
    public function getToggleableFieldsArray() : array
    {
        return $this->getBulkEditableFieldsArray();
    }
}
