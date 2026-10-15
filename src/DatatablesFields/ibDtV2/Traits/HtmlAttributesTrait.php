<?php

namespace IlBronza\Datatables\DatatablesFields\ibDtV2\Traits;

use InvalidArgumentException;

trait HtmlAttributesTrait
{
	public array $inputAttributes = [];
	public array $containerAttributes = [];
	public array $cellAttributes = [];
	public array $htmlClasses = [];
	public array $data = [];
	public ?string $prefix = null;
	public ?string $suffix = null;

	public function addHtmlClass(string $class) : void { $this->htmlClasses[] = $class; }
	public function getHtmlClassesString() : string { return implode(' ', array_unique($this->htmlClasses)); }
	public function getPrefix() : string|false { return $this->prefix ?? false; }
	public function getSuffix() : string|false { return $this->suffix ?? false; }

	protected function javascriptLiteral($value) : string
	{
		return json_encode($value, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_THROW_ON_ERROR);
	}

	protected function escapeHtml($value) : string
	{
		return htmlspecialchars((string) ($value ?? ''), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
	}

	/** A null value omits an attribute; HTML boolean attributes use presence. */
	protected function normalizeHtmlAttributes(array $attributes) : array
	{
		$result = [];
		$booleanAttributes = ['disabled', 'readonly', 'required', 'multiple', 'autofocus', 'hidden', 'checked', 'selected'];

		foreach ($attributes as $name => $value)
		{
			if (! preg_match('/^[a-zA-Z_:][a-zA-Z0-9_.:-]*$/', $name))
				throw new InvalidArgumentException('Invalid HTML attribute: ' . $name);

			if ($value === null || (in_array($name, $booleanAttributes, true) && $value === false))
				continue;

			if (in_array($name, $booleanAttributes, true) && $value === true)
				$value = '';
			elseif (is_bool($value))
				$value = $value ? 'true' : 'false';
			elseif (is_array($value) && $name === 'class')
				$value = implode(' ', $value);
			elseif (! is_scalar($value))
				throw new InvalidArgumentException('HTML attribute ' . $name . ' must be scalar.');

			$result[$name] = (string) $value;
		}

		return $result;
	}

	protected function renderHtmlAttributes(array $attributes) : string
	{
		$html = '';
		foreach ($this->normalizeHtmlAttributes($attributes) as $name => $value)
			$html .= ' ' . $name . '="' . $this->escapeHtml($value) . '"';

		return $html;
	}

	/** Dynamic expressions are supplied by PHP traits, never by cell data. */
	protected function compileHtmlAttributes(array $static, array $expressions = []) : string
	{
		$parts = [$this->javascriptLiteral($this->renderHtmlAttributes(array_diff_key($static, $expressions)))];
		foreach ($expressions as $name => $expression)
		{
			$this->normalizeHtmlAttributes([$name => '']);
			$parts[] = $this->javascriptLiteral(' ' . $name . '="')
				. ' + window.ibDtEscapeHtml(' . $expression . ') + ' . $this->javascriptLiteral('"');
		}

		return implode(' + ', $parts);
	}

	protected function getContainerOpeningHtml(bool $inline = false) : string
	{
		$attributes = $this->containerAttributes;
		if ($this->getPrefix() !== false || $this->getSuffix() !== false)
		{
			$class = $attributes['class'] ?? '';
			$attributes['class'] = trim((is_array($class) ? implode(' ', $class) : $class) . ' ib-suffix-container');
		}

		if ($attributes && $inline)
			$attributes['data-ib-dt-inline-control'] = true;

		return $attributes ? '<div' . $this->renderHtmlAttributes($attributes) . '>' : '';
	}

	protected function getAffixHtml(string $position) : string
	{
		$value = $position === 'prefix' ? $this->getPrefix() : $this->getSuffix();
		return $value === false || $value === null ? ''
			: '<div class="ib-' . $position . '"><div>' . $this->escapeHtml($value) . '</div></div>';
	}

	protected function wrapControlHtml(string $html, bool $inline = false) : string
	{
		$opening = $this->getContainerOpeningHtml($inline);
		return $opening . $this->getAffixHtml('prefix') . $html . $this->getAffixHtml('suffix') . ($opening ? '</div>' : '');
	}

	protected function compileControlWrapper(string $expression) : string
	{
		$opening = $this->getContainerOpeningHtml();
		return $this->javascriptLiteral($opening . $this->getAffixHtml('prefix')) . ' + (' . $expression . ') + '
			. $this->javascriptLiteral($this->getAffixHtml('suffix') . ($opening ? '</div>' : ''));
	}

	protected function compileCellAttributes() : string
	{
		$statements = [];
		foreach ($this->normalizeHtmlAttributes($this->cellAttributes) as $name => $value)
			$statements[] = $name === 'class'
				? 'cell.classList.add(...' . $this->javascriptLiteral($value) . '.split(/\s+/).filter(Boolean));'
				: 'cell.setAttribute(' . $this->javascriptLiteral($name) . ', ' . $this->javascriptLiteral($value) . ');';

		return $statements ? 'createdCell: function(cell) { ' . implode("\n", $statements) . ' },' : '';
	}
}
