<?php

namespace IlBronza\Datatables\DatatablesFields;

class DatatableFieldBoolean extends DatatableField
{
	public null|int|string $keyPosition = null;
	public null|int|string $valuePosition = null;

	public $property;



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

		/**

		uikit icons
		return "
			item = '<span class=\"" . $classes . "\" uk-icon=\"{$iconString}\" ></span>';
		";
		 *
		 * **/

		return "
			item = '<i class=\"fa-solid fa-{$iconString}\"></i>';
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
				" . $this->getBooleanString($this->trueIcon) . "

			else if((item == 0)||(item === false))
				" . $this->getBooleanString($this->falseIcon) . "

			else
				" . $this->getBooleanString($this->nullIcon);

		return "
			if(item)
				" . $this->getBooleanString($this->trueIcon) . "

			else
				" . $this->getVoidString();

	}

	private function _getCustomColumnDefResult()
	{
		return "

		if(item)
			" . $this->getBooleanString($this->trueIcon) . "

		else
			" . $this->getBooleanString($this->falseIcon) . "
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