<?php

namespace IlBronza\Datatables\DatatablesFields\ibDtV2\Traits;

use Illuminate\Support\Str;

trait EditorRoutesTrait
{
	public ?string $customUpdateRouteName = null;
	public ?string $inlineCreateStoreUrl = null;

	public function getEditorUpdateUrl() : string
	{
		$model = $this->getPlaceholderElement();
		if ($this->customUpdateRouteName === null && method_exists($model, 'getEditorUpdateUrl'))
			if ($url = $model->getEditorUpdateUrl())
				return $url;

		$basename = method_exists($model, 'getRouteBasename')
			? $model->getRouteBasename() : Str::plural(lcfirst(class_basename($model)));
		$parameter = method_exists($model, 'getRouteClassname')
			? $model->getRouteClassname() : Str::camel(class_basename($model));

		return route($this->customUpdateRouteName ?? $basename . '.update', [
			$parameter => config('datatables.replace_model_id_string'),
		]);
	}

	public function setInlineEditElement(object $element) : static
	{
		$this->element = $element;
		return $this;
	}

	public function setInlineCreateElement(object $element, string $storeUrl) : static
	{
		$this->element = $element;
		$this->inlineCreateStoreUrl = $storeUrl;
		$this->addHtmlClass('ib-datatable-inline-create-field');
		return $this;
	}

	public function getInlineEditUpdateUrl() : string
	{
		return $this->inlineCreateStoreUrl ?? str_replace(
			config('datatables.replace_model_id_string'),
			rawurlencode((string) $this->element->getKey()),
			$this->getEditorUpdateUrl()
		);
	}
}
