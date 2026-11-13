<?php

namespace IlBronza\Datatables\DatatablesFields\ibDtV2\Editor;

class DatatableFieldTextarea extends DatatableFieldText
{
	public $fieldType = 'textarea';

	protected function isMultilineControl() : bool { return true; }
	protected function getDefaultInputAttributes() : array { return ['rows' => 3]; }
	protected function getSupportedSaveTriggers() : array { return ['blur', 'tab']; }

	protected function getDefaultSaveTrigger() : string
	{
		$trigger = parent::getDefaultSaveTrigger();
		return $trigger === 'enter' ? 'blur' : $trigger;
	}

	public function getInlineFieldType() : string
	{
		return $this->inlineFieldType ?? 'ibDtV2.editor.textarea';
	}

	public function getWidthFromConfig() : string
	{
		return config('datatables.datatableFieldWidths.ibDtV2.editor.datatableFieldTextarea', '250px');
	}
}
