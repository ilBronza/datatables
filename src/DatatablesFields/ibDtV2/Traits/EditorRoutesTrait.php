<?php

namespace IlBronza\Datatables\DatatablesFields\ibDtV2\Traits;

use Illuminate\Support\Str;
use LogicException;
use ReflectionMethod;

trait EditorRoutesTrait
{
	public ?string $customUpdateRouteName = null;
	public ?string $updateUrlMethod = null;
	public ?string $inlineCreateStoreUrl = null;

	public function getEditorRouteConfiguration() : ?array
	{
		return $this->userCanEdit() ? array_replace([
			'urlTemplate' => $this->getEditorUpdateUrl(),
			'idPlaceholder' => config('datatables.replace_model_id_string'),
			'target' => $this->editorTargetsRow() ? 'row' : 'element',
		], $this->updateUrlMethod !== null ? ['updateUrlFromCell' => true] : [],
			$this->editorValueFunction !== null || $this->hasForceValue() ? ['history' => false] : []) : null;
	}

	protected function getElementUpdateUrl(?object $element) : ?string
	{
		if ($this->updateUrlMethod === null || $element === null)
			return null;

		if (! method_exists($element, $this->updateUrlMethod)
			|| ! (new ReflectionMethod($element, $this->updateUrlMethod))->isPublic())
			throw new LogicException('Editor "' . $this->name . '" requires callable updateUrlMethod "' . $this->updateUrlMethod . '" on its model.');

		$url = $element->{$this->updateUrlMethod}();
		if ($url === null)
			return null;
		if (! is_string($url))
			throw new LogicException('Editor "' . $this->name . '" updateUrlMethod "' . $this->updateUrlMethod . '" must return a string or null.');

		return trim($url) === '' ? null : trim($url);
	}

	protected function getInlineContainerAttributes() : array
	{
		$route = $this->inlineCreateStoreUrl !== null
			? ['storeUrl' => $this->inlineCreateStoreUrl]
			: array_replace($this->getEditorRouteConfiguration(), ['targetId' => $this->element->getKey()],
				$this->updateUrlMethod !== null ? ['updateUrl' => $this->getElementUpdateUrl($this->element)] : []);

		$attributes = ['data-ib-dt-editor-route' => $this->javascriptLiteral($route)];
		foreach ($this->getEditorDataAttributes() as $name => $value)
			$attributes['data-' . $name] = $value;
		return $attributes;
	}

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
		return $this->inlineCreateStoreUrl ?? $this->getElementUpdateUrl($this->element) ?? str_replace(
			config('datatables.replace_model_id_string'),
			rawurlencode((string) $this->element->getKey()),
			$this->getEditorUpdateUrl()
		);
	}
}
