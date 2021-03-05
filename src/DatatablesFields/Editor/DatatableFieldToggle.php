<?php

namespace IlBronza\Datatables\DatatablesFields\Editor;

class DatatableFieldToggle extends DatatableFieldEditor
{
	public $trueIcon = 'check';
	public $falseIcon = 'close';
	public $nullable = true;

	public function transformValue($value)
	{
		return $value->{$this->parameter};
	}

	public function getEditorUrl()
	{
		return $this->model::getDatatableEditorUrl();
	}

	private function _getCustomColumnDefNullableResult()
	{
		return "

		if(item)
			item = '<a href=\"javascript:void(0)\" data-url=\"" . $this->getEditorUrl() . "\" uk-icon=\"{$this->trueIcon}\" ></a>';

		else if(item === false)
			item = '<a href=\"javascript:void(0)\" data-url=\"" . $this->getEditorUrl() . "\" uk-icon=\"{$this->falseIcon}\"></a>';

		else
			item = '<a href=\"javascript:void(0)\" data-url=\"" . $this->getEditorUrl() . "\">nd</a>';
			";
	}

	private function _getCustomColumnDefResult()
	{
		return "

		if(item)
			item = '<a href=\"javascript:void(0)\" data-url=\"" . $this->getEditorUrl() . "\" uk-icon=\"{$this->trueIcon}\" ></a>';

		else
			item = '<a href=\"javascript:void(0)\" data-url=\"" . $this->getEditorUrl() . "\" uk-icon=\"{$this->falseIcon}\"></a>';
		";
	}

	public function getCustomColumnDefSingleResult()
	{
		if($this->nullable)
			return $this->_getCustomColumnDefNullableResult();

		return $this->_getCustomColumnDefResult();
	}
}