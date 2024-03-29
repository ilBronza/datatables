<?php

namespace IlBronza\Datatables\DatatablesFields;

class DatatableFieldBoolean extends DatatableField
{
	public $valueAsRowClassPrefix = true;
	public $trueIcon = 'check';
	public $falseIcon = 'close';
	public $nullIcon = 'minus';
	public $width = '45px';
	public $showOnlyTrue = false;
	public $nullable = true;

	public function transformValue($value)
	{
		if(is_null($value))
			return ;

		return !! $value;
	}

	private function getBooleanString(string $iconString)
	{
		$classes = $this->getHtmlClassesString();

		return "
			item = '<span class=\"" . $classes . "\" {$iconString} ></span>';
		";
	}

	private function getVoidString()
	{
		return " item = '';";
	}

	private function _getCustomColumnDefNullableResult()
	{
		if(! $this->showOnlyTrue)
			return "

			if(item)
				" . $this->getBooleanString("uk-icon=\"{$this->trueIcon}\"") . "

			else if((item == 0)||(item === false))
				" . $this->getBooleanString("uk-icon=\"{$this->falseIcon}\"") . "

			else
				" . $this->getBooleanString("uk-icon=\"{$this->nullIcon}\"");

		return "
			if(item)
				" . $this->getBooleanString("uk-icon=\"{$this->trueIcon}\"") . "

			else
				" . $this->getVoidString();

	}

	private function _getCustomColumnDefResult()
	{
		return "

		if(item)
			" . $this->getBooleanString("uk-icon=\"{$this->trueIcon}\"") . "

		else
			" . $this->getBooleanString("uk-icon=\"{$this->falseIcon}\"") . "
		";
	}

    public function getCustomColumnDefSingleSearchResult()
    {
        return "
            return (item)? 1 : 0;
        ";
    }

	public function getCustomColumnDefSingleResult()
	{
		if($this->nullable)
			return $this->_getCustomColumnDefNullableResult();

		return $this->_getCustomColumnDefResult();
	}
}