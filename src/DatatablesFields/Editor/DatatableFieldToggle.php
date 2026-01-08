<?php

namespace IlBronza\Datatables\DatatablesFields\Editor;

class DatatableFieldToggle extends DatatableFieldEditor
{
	public $isButton = true;
	public string $buttonHtmlClass = 'uk-icon-button';
	public $trueIcon = 'check';
	public $falseIcon = 'close';
	public ? bool $nullable = false;

	public $nullIcon = 'minus';

	public $htmlClasses = [
		'ib-toggle'
	];

	public function getButtonHtmlClass()
	{
		return $this->buttonHtmlClass;
	}

	public function isButton() : bool
	{
		return $this->isButton;
	}

	public function getFieldSpecificClasses() : array
	{
		if($this->isButton())
			$this->fieldSpecificClasses[] = $this->getButtonHtmlClass();

		return $this->fieldSpecificClasses;
	}

	private function getLinkString(string $iconString)
	{
		if (! $classes = $this->getHtmlClassesString())
			return ;

		$classString = " class=\"{$classes} fa-solid fa-{$iconString}\" ";

		return "
			item = '<span data-value=\"' + item[1] + '\" " . $this->getParentDataIndexString() . $this->getHtmlDataAttributesString() . $classString . " ></span>';
		";
	}

	private function _getCustomColumnDefNullableResult()
	{
		return "

		" . $this->substituteUrlParameter() . "

		if(item[1])
			" . $this->getLinkString($this->trueIcon) . "

		else if((item[1] === 0)||(item[1] === false))
			" . $this->getLinkString($this->falseIcon) . "

		else
			" . $this->getLinkString($this->nullIcon);
	}

	public function getCustomColumnDefSingleSearchResult()
	{
		return "
			if(item[1])
				item = 1;
			else
				item = 0;
			";
	}

	private function _getCustomColumnDefResult()
	{
		return "

		" . $this->substituteUrlParameter() . "

		if(item[1])
			" . $this->getLinkString($this->trueIcon) . "

		else
			" . $this->getLinkString($this->falseIcon) . "
		";
	}

	public function getCustomColumnDefSingleResult()
	{
		if($this->nullable)
			return $this->_getCustomColumnDefNullableResult();

		return $this->_getCustomColumnDefResult();
	}

	public function getValueAsRowClassScript()
	{
		return "
        //' . $this->name . '
        window.valueAsClass = data[" . $this->getIndex() . "]" . $this->getStructuredDataIndexString() . ";

        $(row).addClass('" . $this->getCompiledAsRowClassPrefix() . "' + data[" . $this->getIndex() . "]" . $this->getStructuredDataIndexString() . ");
        ";
	}

}