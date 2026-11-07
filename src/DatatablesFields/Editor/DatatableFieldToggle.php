<?php

namespace IlBronza\Datatables\DatatablesFields\Editor;

class DatatableFieldToggle extends DatatableFieldEditor
{
	use \IlBronza\Datatables\Traits\DatatablesFields\BooleanStyleTrait;
	public bool $requiresRowSelectCheckbox = true;

	public function isBulkEditable() : bool
	{
		return true;
	}

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

	private function getLinkString(string $iconString, string $state = 'true')
	{
		if (! $classes = $this->getHtmlClassesString())
			return ;

		$icon = $this->booleanStyleIconExpression($state, $iconString);
		$classString = " class=\"{$classes} fa-solid fa-' + {$icon} + '\" ";
		$attributes = addslashes($this->booleanStyleAttributes($state, $iconString));

		return "
			item = '<span {$attributes} data-style-applied-icon=\"' + {$icon} + '\" data-value=\"' + item[1] + '\" " . $this->getParentDataIndexString() . $this->getHtmlDataAttributesString() . $classString . " ></span>';
		";
	}

	private function _getCustomColumnDefNullableResult()
	{
		return "

		" . $this->substituteUrlParameter() . "

		if(item[1])
			" . $this->getLinkString($this->trueIcon) . "

		else if((item[1] === 0)||(item[1] === false))
			" . $this->getLinkString($this->falseIcon, 'false') . "

		else
			" . $this->getLinkString($this->nullIcon, 'null');
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
			" . $this->getLinkString($this->falseIcon, 'false') . "
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

	        window.valueAsClass = '" . $this->getCompiledAsRowClassPrefix() . "' + window.valueAsClass;
	        window.previousValueAsClass = $(row).data(" . json_encode('ibDtValueAsRowClass' . $this->getIndex()) . ");

	        if(window.previousValueAsClass)
	            $(row).removeClass(window.previousValueAsClass);

	        $(row).addClass(window.valueAsClass);
	        $(row).data(" . json_encode('ibDtValueAsRowClass' . $this->getIndex()) . ", window.valueAsClass);
	        ";
	}

}
