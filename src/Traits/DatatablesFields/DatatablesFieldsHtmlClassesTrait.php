<?php

namespace IlBronza\Datatables\Traits\DatatablesFields;

use Illuminate\Support\Str;

use function array_merge;
use function implode;

trait DatatablesFieldsHtmlClassesTrait
{
	public function getValueAsRowClassPrefix()
	{
		if ($this->valueAsRowClassPrefix)
			return Str::slug($this->name);

		return null;
	}

	public function getCompiledAsRowClassPrefix()
	{
		if ($this->compiledAsRowClassPrefix === false)
			return null;

		return $this->compiledAsRowClassPrefix ?? $this->getSluggedName();
	}

	public function addHeaderHtmlClass(string $class)
	{
		if (! in_array($class, $this->headerHtmlClasses))
			$this->headerHtmlClasses[] = $class;
	}

	public function getHeaderHtmlClasses()
	{
		if (! $this->isSortable())
			$this->headerHtmlClasses[] = 'no-sort';

		return implode(' ', $this->headerHtmlClasses);
	}

	public function getFooterHtmlClasses()
	{
		if (! $this->isSortable())
			$this->footerHtmlClasses[] = 'no-sort';

		return implode(' ', $this->footerHtmlClasses);
	}

	public function addHtmlClass(string $htmlClass)
	{
		$this->htmlClasses[] = $htmlClass;
	}

	public function setHtmlClasses(array $parameters = [])
	{
		if ($this->hasTooltip())
			$this->addHtmlClass('tooltip');

		$this->htmlClasses = array_merge(
			$this->htmlClasses, $parameters['htmlClasses'] ?? []
		);
	}

	public function getFieldSpecificClasses() : array
	{
		return $this->fieldSpecificClasses;
	}

	public function getHtmlClasses()
	{
		$pieces = [];

		if ($this->truncateText())
			$pieces[] = 'uk-text-truncate';

		return array_merge(
			$this->htmlClasses, $this->getFieldSpecificClasses(), $pieces
		);
	}

	public function getTDHtmlClasses() : array
	{
		return $this->tDHtmlClasses;
	}

	public function getHtmlClassesString()
	{
		return implode(' ', $this->getHtmlClasses());
	}

	public function getTDHtmlClassesString()
	{
		return implode(' ', $this->getTDHtmlClasses());
	}

	public function getHtmlClassForCss()
	{
		return $this->getCamelName();
	}

	public function getHtmlClass()
	{
		return $this->getHtmlClassesString();
	}
}
