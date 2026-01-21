<?php

namespace IlBronza\Datatables\Traits\DatatablesFields;

use Exception;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

use function array_map;
use function config;
use function explode;
use function get_class;
use function implode;
use function is_null;
use function str_replace;

trait DatatablesFieldsDisplayTrait
{
	public function hasMainHeader() : bool
	{
		return ! ! ($this->mainHeader ?? false);
	}

	public function mustPrintIntestation() : bool
	{
		if (! is_null($this->mustPrintIntestation))
			return $this->mustPrintIntestation;

		if ($table = $this->getTable())
			return $table->mustPrintIntestation();

		return config('datatables.mustPrintIntestation', false);
	}

	public function truncateText() : bool
	{
		return ! ! $this->truncateText;
	}

	public function hasStrLimit() : bool
	{
		return ! ! $this->strLimit;
	}

	public function getStrLimit() : int
	{
		return $this->strLimit;
	}

	public function canBeHidden() : bool
	{
		if (! $this->table->canHideColumns())
			// if(! $this->table->usesColumnDisplay())
			return false;

		return true;
	}

	public function getWidthFromConfig() : string
	{
		$class = get_class($this);

		[$_project, $field] = explode('\\DatatablesFields\\', $class);

		$projectPieces = explode('\\', $_project);

		$project = Str::camel(
			$projectPieces[0] == 'App' ? $projectPieces[0] : $projectPieces[1]
		);

		$pieces = explode('\\', $field);

		$configPieces = array_map(function ($item)
		{
			return Str::camel($item);
		}, $pieces);

		$configString = $project . '.datatableFieldWidths.' . implode('.', $configPieces);

		if ($width = config($configString, false))
			return $width;

		Log::critical("Datatables field width not found for {$configString}");

		// if($this->width)
		// 	return $this->width;

		// if($this->defaultWidth)
		// 	return $this->defaultWidth;

		if ($this->debug())
			throw new Exception("Datatables field width not found for {$configString}");

		return "22em";
	}

	public function manageWidth(array $parameters)
	{
		if (! ($this->width = $parameters['width'] ?? false))
			$this->width = $this->getWidthFromConfig();
	}

	public function getDefaultWidth()
	{
		return $this->getWidth() ?? $this->defaultWidth ?? false;
	}

	public function getWidth()
	{
		$defaultWidth = $this->width ?? config('datatables.widths.' . $this->getType());

		if(! $columnSettings = $this->getTable()->getDatatableUserData()->getColumnSettingsByField($this))
			return $defaultWidth;

		if(! $selectors = $columnSettings['selectors'] ?? false)
			return $defaultWidth;

		if($tdSettings = $selectors['td'] ?? false)
			if(isset($tdSettings['width']))
				return $tdSettings['width'];

		$width = 0;

		foreach($selectors as $selector => $parameters)
			if(isset($parameters['width']))
				$width = max($width, (int) str_replace(['em', 'px', '%'], '', $parameters['width']));

		return $width;
	}

	public function showLabel() : bool
	{
		return ! ! $this->showLabel;
	}

	public function getTranslatedName()
	{
		if (isset($this->translatedName))
			return $this->translatedName;

		return __($this->getTranslationPrefix() . '.' . ($this->forcedStandardName ?? $this->name));
	}

	public function getTranslationPrefix()
	{
		if ($this->translationPrefix)
			return $this->translationPrefix;

		if (! $fieldsGroup = $this->getFieldsGroup())
			return $this->getDefaultTranslationPrefix();

		return $fieldsGroup->getTranslationPrefix();
	}

	public function getDefaultTranslationPrefix()
	{
		return 'fields';
	}

	public function getJsonAjaxExtraData()
	{
		return json_encode($this->fieldExtraData);
	}

	public function getCamelName()
	{
		return Str::camel(str_replace(".", " ", $this->name));
	}

	public function getSluggedName()
	{
		return Str::slug(str_replace(".", " ", $this->name));
	}

	public function renderHeader()
	{
		return view('datatables::datatablesFields._header', ['field' => $this]);
	}

	public function hasTooltip()
	{
		return $this->tooltip;
	}

}
