<?php

namespace IlBronza\Datatables\DatatablesFields;

class DatatableFieldBoolean extends DatatableField
{
	public null|int|string $keyPosition = null;
	public null|int|string $valuePosition = null;

	public $property;


	public bool $nullable = true;

	public $valueAsRowClassPrefix = true;
	public $trueIcon = 'check';
	public $falseIcon = 'close';
	public $nullIcon = 'minus';
	public $showOnlyTrue = false;

	public function transformValue($value)
	{
		if( is_null($value))
			if($this->isNullable())
				return ;

		return !! $value;
	}

	protected function getBooleanString(string $iconString)
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

	public function isNullable()
	{
		if(! is_null($this->nullable))
			return $this->nullable;

		return config('datatables.fields.boolean.nullable');
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
		return '
            return (item)? 1 : 0;
        ';
	}

	public function _getCustomColumnDefSingleSearchResult()
	{
		return '
            item = (item)? "1 true" : "0 false";
        ';
	}

	public function getCustomColumnDefSingleResult()
	{
		if($this->isNullable())
			return $this->_getCustomColumnDefNullableResult();

		return $this->_getCustomColumnDefResult();
	}

	public function getCustomColumnDefSingleResultExport()
	{
		return $this->getCustomColumnDefSingleSearchResult();
	}

}