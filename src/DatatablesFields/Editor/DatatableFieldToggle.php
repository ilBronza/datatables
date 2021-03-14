<?php

namespace IlBronza\Datatables\DatatablesFields\Editor;

class DatatableFieldToggle extends DatatableFieldEditor
{
	public $trueIcon = 'check';
	public $falseIcon = 'close';
	public $nullIcon = 'minus';
	public $width = '25px';
	public $nullable = true;

	private function getLinkString(string $iconString)
	{
		$classes = $this->getHtmlClassesString();

		return "
			item = '<span data-field=\"{$this->parameter}\" class=\"" . $classes . " ib-toggle\" data-url=\"' + url + '\" {$iconString} ></span>';
		";
	}

	private function _getCustomColumnDefNullableResult()
	{
		return "

		" . $this->substituteUrlParameter() . "

		if(item[1])
			" . $this->getLinkString("uk-icon=\"{$this->trueIcon}\"") . "

		else if((item[1] == 0)||(item[1] === false))
			" . $this->getLinkString("uk-icon=\"{$this->falseIcon}\"") . "

		else
			" . $this->getLinkString("uk-icon=\"{$this->nullIcon}\"");
	}

	private function _getCustomColumnDefResult()
	{
		return "

		" . $this->substituteUrlParameter() . "

		if(item[1])
			" . $this->getLinkString("uk-icon=\"{$this->trueIcon}\"") . "

		else
			" . $this->getLinkString("uk-icon=\"{$this->falseIcon}\"") . "
		";
	}

	public function getCustomColumnDefSingleResult()
	{
		if($this->nullable)
			return $this->_getCustomColumnDefNullableResult();

		return $this->_getCustomColumnDefResult();
	}
}