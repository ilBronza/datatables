<?php

namespace IlBronza\Datatables\DatatablesFields\ibDtV2\Traits;

trait InputControlTrait
{
	public function getEditorFieldType()
	{
		return $this->fieldType;
	}

	protected function getInputAttributes(bool $inline = false) : array
	{
		$attributes = $this->inputAttributes;
		foreach ($this->data as $name => $value)
			$attributes['data-' . $name] = $value;

		$customClasses = $attributes['class'] ?? '';
		if (is_array($customClasses))
			$customClasses = implode(' ', $customClasses);

		$attributes = array_replace($attributes, [
			'type' => $this->getEditorFieldType(),
			'class' => trim($customClasses . ' ' . $this->getHtmlClassesString() . ' uk-input ib-editor-text ib-dt-editor-v2'
				. ($inline ? ' ib-datatable-inline-edit-field' : '')),
			'data-field' => $this->getFieldSpecificData()['field'],
			'data-ib-dt-cell-format' => $this->getSaveSettings()['cellFormat'],
			'data-ib-dt-value-type' => $this->editorValueType,
			'data-save-trigger' => $this->getSaveSettings()['saveTrigger'],
		]);
		unset($attributes['disabled'], $attributes['value'], $attributes['data-originalvalue'], $attributes['data-url']);

		if ($inline)
			$attributes['data-inline-source-field'] = $this->name;

		return $attributes;
	}

	public function getInlineEditValue() : string
	{
		return (string) ($this->readEditorValue($this->element, true) ?? '');
	}

	public function getInlineEditHtml() : string
	{
		if (! $this->userCanEdit())
			return '';

		$value = $this->getInlineEditValue();
		$attributes = array_replace($this->getInputAttributes(true), [
			'value' => $value,
			'data-originalvalue' => $value,
			'data-url' => $this->getInlineEditUpdateUrl(),
			'disabled' => $this->isDisabledForElement($this->element),
		]);

		return $this->wrapControlHtml('<input' . $this->renderHtmlAttributes($attributes) . ' />', true);
	}

	/** Assigns item only: the column/iterator owns the final return. */
	public function getCustomColumnDefSingleResult()
	{
		$value = $this->getCellValueExpression();
		if (! $this->userCanEdit())
			return 'item = ' . $this->compileControlWrapper('window.ibDtEscapeHtml(' . $value . ')') . ';';

		$target = $this->getTargetIdExpression();
		$url = $this->javascriptLiteral($this->getEditorUpdateUrl());
		$placeholder = $this->javascriptLiteral(config('datatables.replace_model_id_string'));
		$expressions = [
			'value' => 'value',
			'data-originalvalue' => 'value',
			'data-url' => 'url',
		];
		if (! $this->editorTargetsRow())
			$expressions['data-ib-dt-target-id'] = 'targetId';
		$attributes = $this->compileHtmlAttributes($this->getInputAttributes(), $expressions);
		$input = '"<input" + (' . $attributes . ') + ' . $this->compileDisabledAttribute() . ' + " />"';
		$control = $this->compileControlWrapper($input);

		return "{
			const value = {$value};
			const targetId = {$target};
			const url = {$url}.split({$placeholder}).join(encodeURIComponent(String(targetId ?? '')));
			item = {$control};
		}";
	}
}
