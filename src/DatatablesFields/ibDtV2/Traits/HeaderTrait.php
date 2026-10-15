<?php

namespace IlBronza\Datatables\DatatablesFields\ibDtV2\Traits;

use IlBronza\Buttons\Icons\FaIcon;

/** Column labels and text-filter markup consumed by the table's Blade views. */
trait HeaderTrait
{
	public ?string $translatedName = null;
	public ?string $translationPrefix = null;
	public ?string $forcedStandardName = null;
	public ?string $mainHeader = null;
	public bool $showLabel = true;
	public ?bool $mustPrintIntestation = null;
	public bool $headerTooltip = false;
	public ?string $translatedHeaderTooltip = null;
	public ?string $intestationIcon = null;
	public array $headerHtmlClasses = [];
	public array $footerHtmlClasses = [];
	public array $headerData = [];
	public array $footerData = [];
	public ?string $confirmMessage = null;
	public bool $filterable = true;
	public bool $canDrawTable = true;
	public ?array $jqueryFilterEvents = null;

	public function getTranslationPrefix() : string
	{
		return $this->translationPrefix ?? $this->fieldsGroup?->getTranslationPrefix() ?? 'fields';
	}

	public function getTranslatedName() : string
	{
		return $this->translatedName ?? __($this->getTranslationPrefix() . '.' . ($this->forcedStandardName ?? $this->name));
	}

	public function getHeaderTooltip() : ?string
	{
		if ($this->translatedHeaderTooltip !== null || ! $this->headerTooltip)
			return $this->translatedHeaderTooltip;

		$parts = explode('::', $this->getTranslationPrefix());
		$prefix = count($parts) > 1 ? $parts[0] . '::tooltips' : 'tooltips';
		return __($prefix . '.' . ($this->forcedStandardName ?? $this->name));
	}

	public function getHeaderHtmlClasses() : string
	{
		return implode(' ', array_merge($this->headerHtmlClasses, $this->getGroupClasses(), $this->sortable ? [] : ['no-sort']));
	}

	public function getFooterHtmlClasses() : string
	{
		return implode(' ', array_merge($this->footerHtmlClasses, $this->getGroupClasses(), $this->sortable ? [] : ['no-sort']));
	}

	public function getHeaderData() : array
	{
		return $this->confirmMessage === null ? $this->headerData : array_replace($this->headerData, ['confirm' => __($this->confirmMessage)]);
	}

	public function getFooterData() : array { return $this->footerData; }
	public function hasMainHeader() : bool { return (bool) $this->mainHeader; }
	public function showLabel() : bool { return $this->showLabel; }
	public function isFilterable() : bool { return $this->filterable; }
	public function getFilterType() : string { return $this->filterable ? 'text' : 'none'; }
	public function canDrawTable() : bool { return $this->canDrawTable; }
	public function canDrawKeyup() : bool { return $this->canDrawTable && (bool) $this->table?->drawOnFieldsEvents(); }
	public function getInstationIconString() : ?string { return $this->intestationIcon ? FaIcon::inline($this->intestationIcon) : null; }

	public function mustPrintIntestation() : bool
	{
		return $this->mustPrintIntestation ?? $this->table?->mustPrintIntestation() ?? config('datatables.mustPrintIntestation', false);
	}

	public function getJqueryFilterEventsString() : string
	{
		return implode(' ', $this->jqueryFilterEvents ?? match (config('datatables.filterTrigger', 'enter')) {
			'blur' => ['change', 'blur'],
			'keyup' => ['change', 'keyup'],
			default => ['change', 'enter'],
		});
	}
}
