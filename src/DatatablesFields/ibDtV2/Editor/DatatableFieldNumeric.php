<?php

namespace IlBronza\Datatables\DatatablesFields\ibDtV2\Editor;

use IlBronza\Datatables\Contracts\DatatableFieldContract;
use IlBronza\Datatables\Datatables;
use IlBronza\Datatables\DatatablesFields\ibDtV2\Traits\NumericOptionsTrait;
use IlBronza\Datatables\Support\DecimalValue;

class DatatableFieldNumeric extends DatatableFieldText
{
	use NumericOptionsTrait;

	public string $editorValueType = 'decimal';
	public ?string $datatableType = 'ib-decimal';
	public ?string $textAlign = 'right';

	public function __construct(string $name, array $parameters = [], ?int $index = null, ?DatatableFieldContract $parent = null, ?Datatables $table = null)
	{
		parent::__construct($name, $parameters, $index, $parent, $table);
		$this->getNumericOptions();
	}

	protected function getDefaultInputAttributes() : array
	{
		return ['class' => 'ib-dt-numeric-editor', 'inputmode' => $this->maxDecimals === 0 ? 'numeric' : 'decimal'];
	}

	protected function readEditorValue(object $element, bool $inline = false)
	{
		return DecimalValue::canonical(parent::readEditorValue($element, $inline));
	}

	public function getEditorDataAttributes() : array
	{
		return array_replace(parent::getEditorDataAttributes(), ['ib-dt-number-options' => $this->javascriptLiteral($this->getNumericOptions())]);
	}

	public function getFieldSpecificData() : array
	{
		return array_replace(parent::getFieldSpecificData(), ['number-options' => $this->getNumericOptions()]);
	}

	protected function getInputDisplayValueExpression(string $value) : string
	{
		return 'window.IlBronza.datatables.editors.helpers.numbers.format(' . $value . ', ' . $this->javascriptLiteral($this->getNumericOptions()) . ')';
	}

	public function getSuffix() : string|false { return $this->suffix ?? $this->currency ?? false; }
	public function getInlineFieldType() : string { return $this->inlineFieldType ?? 'ibDtV2.editor.numeric'; }
	public function getWidthFromConfig() : string
	{
		return config('datatables.datatableFieldWidths.ibDtV2.editor.datatableFieldNumeric', '125px');
	}
}
