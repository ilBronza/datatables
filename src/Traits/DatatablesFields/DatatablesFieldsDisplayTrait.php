<?php

namespace IlBronza\Datatables\Traits\DatatablesFields;

use Illuminate\Support\Str;

trait DatatablesFieldsDisplayTrait
{
	public function truncateText() : bool
	{
		return !! $this->truncateText;
	}

    public function hasStrLimit() : bool
    {
        return !! $this->strLimit;
    }

    public function getStrLimit() : int
    {
        return $this->strLimit;
    }

    public function canBeHidden() : bool
    {
        if(! $this->table->canHideColumns())
        // if(! $this->table->usesColumnDisplay())
            return false;

        return true;
    }

    public function getDefaultWidth()
    {
        return $this->getWidth() ?? $this->defaultWidth ?? false;
    }

    public function getWidth()
    {
        return $this->width ?? false;
    }

    public function manageWidth(array $parameters)
    {
        if($parameters['width'] ?? false)
            return;

        if($width = $this->getDefaultWidth())
            $this->width = $width;
    }

    public function getDefaultTranslationPrefix()
    {
        return 'fields';
    }

    public function getTranslationPrefix()
    {
        if($this->translationPrefix)
            return $this->translationPrefix;

        if(! $fieldsGroup = $this->getFieldsGroup())
            return $this->getDefaultTranslationPrefix();

        return $fieldsGroup->getTranslationPrefix();
    }

    public function getTranslatedName()
    {
		if(isset($this->translatedName))
			return $this->translatedName;

		return __($this->getTranslationPrefix() . '.' . $this->name);
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
