<?php

namespace IlBronza\Datatables\Traits;

use IlBronza\Datatables\DatatablesFields\DatatableField;
use IlBronza\Datatables\DatatablesFields\Editor\DatatableFieldEditor;
use Illuminate\Support\Collection;

use function config;
use function is_null;

trait DatatableSelectRowsTrait
{
    protected ?bool $bulkEditFromController = null;
    protected ?bool $bulkEditOnSelectionFromController = null;

    public function setBulkEditFromController(?bool $bulkEdit) : self
    {
        $this->bulkEditFromController = $bulkEdit;

        return $this;
    }

    public function getBulkEdit() : bool
    {
        if (! is_null($this->bulkEdit))
            return (bool) $this->bulkEdit;

        if (! is_null($this->bulkEditFromController))
            return (bool) $this->bulkEditFromController;

        return (bool) config('datatables.bulkEdit', false);
    }

    public function hasBulkEdit() : bool
    {
        return $this->getBulkEdit();
    }

    public function setBulkEditOnSelectionFromController(?bool $bulkEditOnSelection) : self
    {
        $this->bulkEditOnSelectionFromController = $bulkEditOnSelection;

        return $this;
    }

    public function getBulkEditOnSelection() : bool
    {
        if (! is_null($this->bulkEditOnSelection))
            return (bool) $this->bulkEditOnSelection;

        if (! is_null($this->bulkEditOnSelectionFromController))
            return (bool) $this->bulkEditOnSelectionFromController;

        return (bool) config('datatables.bulkEditOnSelection', false);
    }

    public function hasBulkEditOnSelection() : bool
    {
        return $this->getBulkEditOnSelection();
    }

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
        if ($this->hasSelectRowCheckboxes())
            return;

        $fieldsGroup = $this->createFieldsGroup('selectRow');

        $selectRowField = $this->addField('selectRow', [
			"type" => "selectRowCheckbox"
        ]);

        $fieldsGroup->addField('selectRow', $selectRowField);

        if (! isset($this->fields['mySelfPrimary']))
        {
            $primaryField = $this->addField('mySelfPrimary', [
                'type' => 'primary',
                'forcedStandardName' => 'mySelfPrimary',
            ]);
            $fieldsGroup->addField('mySelfPrimary', $primaryField);
        }

        $this->selectRowCheckboxes = true;

        $this->condensateIndexes();
        $this->parseRowId();
    }

    public function fieldsGroupsRequiresSelectRowCheckboxes(array $fieldsGroups) : bool
    {
        foreach($fieldsGroups as $fieldsGroup)
            foreach($fieldsGroup['fields'] as $name => $parameters)
            {
                $field = $this->craeteField($name, $parameters);

                if ($field->requiresSelectRowCheckboxes())
                    return true;

                if (($this->hasBulkEdit() || $this->hasBulkEditOnSelection()) && $field->isBulkEditable())
                    return true;
            }

        return false;
    }

    public function getBulkEditableFields() : Collection
    {
        if (! ($this->hasBulkEdit() || $this->hasBulkEditOnSelection()))
            return collect();

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
                'nullable' => $field instanceof DatatableFieldEditor && $field->isNullable(),
                'customValueMode' => $fieldData['custom-value-mode'] ?? false,
            ];
        })->values()->all();
    }

    public function hasBulkEditButton() : bool
    {
        return $this->hasBulkEdit()
            && $this->hasSelectRowCheckboxes()
            && $this->hasBulkEditableFields();
    }

    public function hasBulkEditOnSelectionButton() : bool
    {
        return $this->hasBulkEditOnSelection()
            && $this->hasSelectRowCheckboxes()
            && $this->hasBulkEditableFields();
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
