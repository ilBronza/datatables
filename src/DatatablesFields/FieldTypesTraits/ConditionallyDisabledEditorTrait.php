<?php

namespace IlBronza\Datatables\DatatablesFields\FieldTypesTraits;

use LogicException;

trait ConditionallyDisabledEditorTrait
{
	public ?string $conditionallyDisabledMethod = null;

	abstract protected function getConditionallyDisabledCellIndex() : int;
	abstract protected function getConditionallyDisabledHtmlTag() : string;

	protected function getConditionallyDisabledDisplayTags() : array
	{
		return [$this->getConditionallyDisabledHtmlTag()];
	}

	public function isBulkEditable() : bool
	{
		return false;
	}

	protected function isConditionallyDisabled() : bool
	{
		$method = $this->conditionallyDisabledMethod;

		if (! $method || ! is_object($this->element) || ! is_callable([$this->element, $method]))
			throw new LogicException(sprintf(
				'Il campo editor "%s" richiede conditionallyDisabledMethod "%s" chiamabile sul model della riga',
				$this->name,
				$method
			));

		return (bool) $this->element->{$method}();
	}

	public function transformValue($value)
	{
		$result = parent::transformValue($value);
		$result[] = $this->isConditionallyDisabled();

		return $result;
	}

	public function getCustomColumnDefSingleResult()
	{
		if (! $this->userCanEdit())
			return parent::getCustomColumnDefSingleResult();

		$index = $this->getConditionallyDisabledCellIndex();
		$disableControl = '';

		foreach ($this->getConditionallyDisabledDisplayTags() as $tag)
			$disableControl .= "item = item.replace('<{$tag} ', '<{$tag} disabled ');\n";

		return "
		let conditionallyDisabledEditor = item && item[{$index}] === true;
		" . parent::getCustomColumnDefSingleResult() . "
		if (conditionallyDisabledEditor)
		{
			{$disableControl}
		}
		";
	}

	public function getInlineEditHtml() : string
	{
		$html = parent::getInlineEditHtml();

		if ($html === '' || ! $this->isConditionallyDisabled())
			return $html;

		$tag = $this->getConditionallyDisabledHtmlTag();

		return preg_replace('/^<' . $tag . ' /', '<' . $tag . ' disabled ', $html);
	}
}
