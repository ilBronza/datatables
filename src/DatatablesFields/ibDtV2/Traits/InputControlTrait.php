<?php

namespace IlBronza\Datatables\DatatablesFields\ibDtV2\Traits;

trait InputControlTrait
{
	protected function isMultilineControl() : bool { return false; }
	protected function getDefaultInputAttributes() : array { return []; }
	protected function getInputDisplayValueExpression(string $value) : string { return $value; }

	public function getEditorFieldType()
	{
		return $this->fieldType;
	}

	protected function getInputAttributes(bool $inline = false) : array
	{
		$defaults = $this->getDefaultInputAttributes();
		$attributes = array_replace($defaults, $this->inputAttributes);
		$attributes['class'] = array_merge((array) ($defaults['class'] ?? []), (array) ($this->inputAttributes['class'] ?? []));
		foreach ($this->data as $name => $value)
			$attributes['data-' . $name] = $value;

		$customClasses = $attributes['class'] ?? '';
		if (is_array($customClasses))
			$customClasses = implode(' ', $customClasses);

		$attributes = array_replace($attributes, [
			'type' => $this->getEditorFieldType(),
			'class' => trim($customClasses . ' ' . $this->getHtmlClassesString()
				. ($this->isMultilineControl() ? ' uk-textarea' : ' uk-input') . ' ib-editor-text ib-dt-editor-v2'
				. ($inline ? ' ib-datatable-inline-edit-field' : '')),
		]);
		unset($attributes['disabled'], $attributes['value'], $attributes['data-originalvalue'],
			$attributes['data-url'], $attributes['data-ib-dt-editor-route'], $attributes['data-save-trigger'],
			$attributes['data-field'], $attributes['data-ib-dt-cell-format'], $attributes['data-ib-dt-value-type']);
		if ($this->isMultilineControl())
			unset($attributes['type']);

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
			'disabled' => $this->isDisabledForElement($this->element),
		]);

		return $this->wrapControlHtml($this->renderInputControl($attributes, $value), true);
	}

	protected function renderInputControl(array $attributes, string $value) : string
	{
		if (! $this->isMultilineControl())
			return '<input' . $this->renderHtmlAttributes($attributes) . ' />';

		unset($attributes['value']);
		// HTML consumes the first LF after <textarea>; prepend one to preserve the value.
		return '<textarea' . $this->renderHtmlAttributes($attributes) . ">\n" . $this->escapeHtml($value) . '</textarea>';
	}

	/** Assigns item only: the column/iterator owns the final return. */
	public function getCustomColumnDefSingleResult()
	{
		$value = $this->getCellValueExpression();
		if (! $this->userCanEdit())
			return 'item = ' . $this->compileControlWrapper('window.IlBronza.datatables.editors.helpers.escapeHtml(' . $this->getInputDisplayValueExpression($value) . ')') . ';';

		$target = $this->getTargetIdExpression();
		$expressions = [
			'data-originalvalue' => 'value',
		];
		if (! $this->isMultilineControl())
			$expressions['value'] = $this->getInputDisplayValueExpression('value');
		if (! $this->editorTargetsRow())
			$expressions['data-ib-dt-target-id'] = 'targetId';
		$attributes = $this->compileHtmlAttributes($this->getInputAttributes(), $expressions);
		$tag = $this->isMultilineControl() ? 'textarea' : 'input';
		$ending = $this->isMultilineControl()
			? $this->javascriptLiteral(">\n") . ' + window.IlBronza.datatables.editors.helpers.escapeHtml(value) + "</textarea>"'
			: '" />"';
		$input = $this->javascriptLiteral('<' . $tag) . ' + (' . $attributes . ') + ' . $this->compileDisabledAttribute() . ' + ' . $ending;
		$control = $this->compileControlWrapper($input);

		return "{
			const value = {$value};
			const targetId = {$target};
			item = {$control};
		}";
	}
}
