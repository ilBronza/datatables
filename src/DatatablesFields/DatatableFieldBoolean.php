<?php

namespace IlBronza\Datatables\DatatablesFields;

class DatatableFieldBoolean extends DatatableField
{
	use \IlBronza\Datatables\Traits\DatatablesFields\BooleanStyleTrait;
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

		return "item = '<i class=\"fa-{$this->iconType} fa-{$iconString}\"></i>';";
	}

	private function getStateBooleanString(string $iconString, string $state = 'true')
	{
		$attributes = addslashes($this->booleanStyleAttributes($state, $iconString));
		$icon = $this->booleanStyleIconExpression($state, $iconString);
		$renderer = $this->getBooleanString($iconString);
		$renderer = str_replace('class="', $attributes . " data-style-applied-icon=\"' + {$icon} + '\" class=\"", $renderer);
		return str_replace('fa-' . $iconString . '"', "fa-' + {$icon} + '\"", $renderer);
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
				" . $this->getStateBooleanString($this->trueIcon) . "

			else if((item == 0)||(item === false))
				" . $this->getStateBooleanString($this->falseIcon, 'false') . "

			else
				" . $this->getStateBooleanString($this->nullIcon, 'null');

		return "
			if(item)
				" . $this->getStateBooleanString($this->trueIcon) . "

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
			" . $this->getStateBooleanString($this->trueIcon) . "

		else
			" . $this->getStateBooleanString($this->falseIcon, 'false') . "
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
