<?php

namespace IlBronza\Datatables\DatatablesFields\ibDtV2\Traits;

use InvalidArgumentException;
use LogicException;

trait EditorBindingTrait
{
	public ?string $editorProperty = null;
	public ?string $editorValueFunction = null;
	public ?string $parameter = null;
	public ?string $inlineEditProperty = null;
	public ?string $inlineFieldType = null;
	/** null infers row for direct fields, element for children / solveElement. */
	public ?string $editorTarget = null;
	public ?string $saveTrigger = null;

	protected function initializeEditorBinding() : void
	{
		if (! in_array($this->editorTarget, [null, 'row', 'element'], true))
			throw new InvalidArgumentException('editorTarget must be row or element.');

		if (! in_array($this->getSaveSettings()['saveTrigger'], ['enter', 'blur', 'tab'], true))
			throw new InvalidArgumentException('saveTrigger must be enter, blur or tab.');

		$this->parameter ??= $this->name;
		$this->setReloadTableExtraData($this->reloadTable);
		// Re-evaluate model-dependent state after saving unless explicitly disabled.
		$this->setRefreshRowExtraData($this->refreshRow ?? ($this->disabledIf !== null ? true : null));
		if ($this->hasSpinner())
			$this->addHtmlClass('spin');
	}

	public function isEditor() : bool
	{
		return true;
	}

	public function editorTargetsRow() : bool
	{
		return ($this->editorTarget ?? ($this->hasParent() || isset($this->solveElement) ? 'element' : 'row')) === 'row';
	}

	public function getValuePosition() : null|int|string
	{
		return $this->disabledIf !== null || ! $this->editorTargetsRow() ? 'value' : null;
	}

	public function getSaveSettings() : array
	{
		return [
			'saveTrigger' => $this->saveTrigger ?? config('datatables.editor.saveTrigger', 'enter'),
			'cellFormat' => $this->getValuePosition() === null ? 'scalar' : 'value',
		];
	}

	public function getFieldSpecificData() : array
	{
		return ['field' => $this->parameter ?? $this->name];
	}

	protected function readEditorValue(object $element, bool $inline = false)
	{
		if ($this->hasForceValue())
			return $this->forceValue;
		if ($inline && $this->inlineEditProperty !== null)
			return data_get($element, $this->inlineEditProperty);
		if ($this->editorValueFunction)
			return $element->{$this->editorValueFunction}();

		return data_get($element, $this->editorProperty ?? $this->name);
	}

	public function transformValue($value)
	{
		if (isset($this->solveElement))
			$value = $this->getFieldCellDataValue($this->name, $value);

		$this->element = $value;
		$cellValue = $value === null ? null : $this->readEditorValue($value);
		if ($this->getValuePosition() === null)
			return $cellValue;

		$cell = ['value' => $cellValue];
		if ($this->disabledIf !== null)
			$cell['disabled'] = $value === null || $this->isDisabledForElement($value);
		if (! $this->editorTargetsRow())
			$cell['targetId'] = $value?->getKey();

		return $cell;
	}

	protected function getTargetIdExpression() : string
	{
		if (! $this->editorTargetsRow())
			return 'item.targetId';

		$index = $this->table?->getRowIdIndex();
		if ($index === null || $index === false)
			throw new LogicException('Editor "' . $this->name . '" requires a rowId column.');

		return 'row[' . $this->javascriptLiteral($index) . ']';
	}

	public function isBulkEditable() : bool
	{
		return $this->userCanEdit() && ! $this->disabled && $this->disabledIf === null && $this->editorTargetsRow();
	}

	public function getInlineFieldType() : string
	{
		return $this->inlineFieldType ?? 'ibDtV2.editor.text';
	}

}
