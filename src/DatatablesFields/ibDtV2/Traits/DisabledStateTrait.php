<?php

namespace IlBronza\Datatables\DatatablesFields\ibDtV2\Traits;

use LogicException;
use ReflectionMethod;

trait DisabledStateTrait
{
	public bool $disabled = false;
	public ?string $disabledIf = null;

	protected function isDisabledForElement(object $element) : bool
	{
		if ($this->disabledIf === null)
			return $this->disabled;

		// Eloquent's __call makes is_callable() true even for unavailable methods.
		if (! method_exists($element, $this->disabledIf) || ! (new ReflectionMethod($element, $this->disabledIf))->isPublic())
			throw new LogicException('Editor "' . $this->name . '" requires callable disabledIf "' . $this->disabledIf . '" on its model.');

		return (bool) $element->{$this->disabledIf}() || $this->disabled;
	}

	protected function compileDisabledAttribute() : string
	{
		if ($this->disabled)
			return $this->javascriptLiteral(' disabled');

		$condition = 'targetId == null';
		if ($this->disabledIf !== null)
			$condition .= ' || item.disabled';

		return '(' . $condition . ' ? " disabled" : "")';
	}
}
