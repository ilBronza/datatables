<?php

namespace IlBronza\Datatables\Traits;

use IlBronza\Datatables\DatatablesFields\Editor\DatatableFieldToggle;
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
                if($this->craeteField($name, $parameters)->requiresSelectRowCheckboxes())
                    return true;

        return false;
    }

    public function getToggleableFields() : Collection
    {
        return $this->getFields()->filter(
            fn ($field) => $field instanceof DatatableFieldToggle
        );
    }

    public function hasToggleableFields() : bool
    {
        return $this->getToggleableFields()->isNotEmpty();
    }

    public function getToggleableFieldsArray() : array
    {
        return $this->getToggleableFields()->map(function (DatatableFieldToggle $field)
        {
            $fieldData = $field->getFieldSpecificData();

            return [
                'name' => $field->getFieldName(),
                'parameter' => $fieldData['field'] ?? $field->getFieldName(),
                'index' => $field->getIndex(),
                'nullable' => $field->isNullable(),
                'label' => $field->getTranslatedName(),
                'updateUrl' => $field->getEditorUpdateUrl(),
            ];
        })->values()->all();
    }

    public function hasBulkToggleButton() : bool
    {
        return $this->hasSelectRowCheckboxes() && $this->hasToggleableFields();
    }
}