<?php

namespace IlBronza\Datatables\DatatablesFields\ibDtV2\Traits;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

trait ColumnOptionsTrait
{
	public ?string $width = null;
	public bool $sortable = true;
	public ?array $order = null;
	public ?bool $visible = null;
	public ?string $datatableType = null;
	public ?string $orderDataType = null;
	public array $tDHtmlClasses = [];
	public array $cssRules = [];
	public ?string $textAlign = null;
	public array $fieldsGroupsDefinitions = [];
	public array $holdFieldsGroupsDefinitions = [];

	public function getUnstyledWidth() : string
	{
		return $this->width ?? $this->getWidthFromConfig();
	}

	public function getWidth() : string
	{
		$settings = $this->table?->getDatatableUserData()->getColumnSettingsByField($this) ?? [];
		return $settings['selectors']['td']['width'] ?? $this->getUnstyledWidth();
	}

	public function isVisible() : bool
	{
		if ($this->getHoldFieldsGroupsDefinitions())
			return false;
		if ($this->table?->canHideColumns() && Auth::user())
		{
			$preference = $this->table->getDatatableUserData()->getcolumnsItem($this->name)->visible;
			if ($preference !== null)
				return (bool) $preference;
		}
		return $this->visible ?? true;
	}

	public function getColumnDefs() : array
	{
		return array_filter([
			'width' => $this->width ?? $this->getWidthFromConfig(),
			'visible' => $this->isVisible(),
			'orderable' => $this->sortable,
			'orderDataType' => $this->orderDataType,
			'type' => $this->datatableType,
			'className' => trim($this->getCamelName() . ' ' . implode(' ', $this->tDHtmlClasses)
				. ' ' . Str::slug($this->getTranslatedName())),
		], static fn ($value) => $value !== null);
	}

	public function getColumnOptions() : array { return $this->order === null ? [] : ['order' => $this->order]; }
	public function isSortable() : bool { return $this->sortable; }
	public function canBeHidden() : bool { return (bool) $this->table?->canHideColumns(); }
	public function getHtmlClassForCss() : string { return $this->getCamelName(); }
	public function getCssRules() : array { return $this->cssRules; }
	public function hasCss() : bool { return $this->cssRules !== []; }
	public function getAlignmentCssString() : ?string { return $this->textAlign === null ? null : 'text-align: ' . $this->textAlign . ';'; }
	public function getFieldsGroupsDefinitions() : array { return $this->normalizeGroupNames($this->fieldsGroupsDefinitions); }
	public function getHoldFieldsGroupsDefinitions() : array { return $this->normalizeGroupNames($this->holdFieldsGroupsDefinitions); }

	private function normalizeGroupNames(array $groups) : array
	{
		$names = [];
		foreach ($groups as $key => $value)
		{
			$name = is_string($value) ? trim($value) : (is_string($key) && $value ? trim($key) : '');
			if ($name !== '')
				$names[] = $name;
		}
		return array_values(array_unique($names));
	}

	protected function getGroupClasses() : array
	{
		return array_map(static fn ($name) => 'ib-dt-fieldsgroup-' . Str::slug($name),
			array_unique(array_merge($this->getFieldsGroupsDefinitions(), $this->getHoldFieldsGroupsDefinitions())));
	}
}
