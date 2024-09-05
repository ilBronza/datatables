<?php

namespace IlBronza\Datatables\DatatablesFields\Editor;

use Auth;
use Exception;
use IlBronza\AccountManager\Models\Role;
use IlBronza\Datatables\Datatables;
use IlBronza\Datatables\DatatablesFields\DatatableField;
use IlBronza\Datatables\DatatablesFields\FieldTypesTraits\IconTextContentTrait;
use Illuminate\Support\Str;

use function config;
use function is_null;

class DatatableFieldEditor extends DatatableField
{
	use IconTextContentTrait;

	public $ajax = true;
	public $spin = true;
	public $editorProperty;

	public ?bool $nullable = true;

	public ?bool $refreshRow = null;
	public ?bool $reloadTable = null;
	public $requireElement = true;
	public $requiresPlaceholderElement = true;
	public $customUpdateRouteName = false;
	public array $editorRoles = [];

	//defines if vlaue is retrieved by a methd called on field element
	public $editorValueFunction = false;

	public function __construct(string $name, array $parameters = [], int $index = null, DatatableField $parent = null, Datatables $table = null)
	{
		parent::__construct($name, $parameters, $index, $parent, $table);

		$this->setParameterByName();

		$this->setReloadTableExtraData($this->reloadTable);
		$this->setRefreshRowExtraData($this->refreshRow);
	}

	/**
	 * if parameter to toggle is not declared in model's array, field name is taken as default
	 **/
	private function setParameterByName()
	{
		if (! isset($this->parameter))
			$this->parameter = $this->name;
	}

	public function setReloadTableExtraData(bool $reloadTable = null)
	{
		if (is_null($reloadTable))
			$reloadTable = config('datatables.editor.reloadTable', false);

		$this->addExtradata('reloadTable', $reloadTable);
	}

	public function setRefreshRowExtraData(bool $refreshRow = null)
	{
		if ($this->getReloadTable())
			return $this->addExtradata('refreshRow', false);

		if (is_null($refreshRow))
			$refreshRow = config('datatables.editor.refreshRow', false);

		$this->addExtradata('refreshRow', $refreshRow);
	}

	public function getReloadTable() : bool
	{
		if (! is_null($this->reloadTable))
			return $this->reloadTable;

		return config('datatables.editor.reloadTable', false);
	}

	public function isNullable() : bool
	{
		return ! ! $this->nullable;
	}

	public function userCanEdit()
	{
		if (! $this->hasEditorRoles())
			return true;

		if (! $user = Auth::user())
			return false;

		foreach ($user->roles as $role)
			if ($this->isEditorAllowedForRole($role))
				return true;

		return false;
	}

	public function hasEditorRoles() : bool
	{
		return count($this->getEditorRoles()) > 0;
	}

	public function getEditorRoles() : array
	{
		return $this->editorRoles;
	}

	public function isEditorAllowedForRole(Role $role)
	{
		return in_array($role->name, $this->getEditorRoles());
	}

	public function getFieldSpecificData() : array
	{
		if ($this->editorProperty ?? false)
			return [
				'field' => $this->editorProperty
			];

		if ($this->parameter ?? false)
			return [
				'field' => $this->parameter
			];

		return [];
	}

	public function setHtmlClasses(array $parameters = [])
	{
		parent::setHtmlClasses($parameters);

		if ($this->hasSpinner())
			$this->addHtmlClass('spin');
	}

	public function hasSpinner()
	{
		return $this->spin;
	}

	public function transformValue($value)
	{
		if ($this->hasForceValue())
		{
			if (! $this->requireElement())
				return $value;

			return [
				$value->getKey(),
				$this->forceValue
			];
		}

		if (isset($this->solveElement))
			$value = $this->getFieldCellDataValue($this->name, $value);

		if (! $this->requireElement())
			return $value;

		$this->element = $value;

		if ($this->editorValueFunction)
			return [
				$this->element->getKey(),
				$this->element->{$this->editorValueFunction}()
			];

		$propertyName = $this->editorProperty ?? $this->name;

		return [
			$this->element->getKey(),
			$value->{$propertyName}
		];
	}

	public function getEndingResultOptions() : ?string
	{
		if ($this->getSuffix() || $this->getPrefix())
			return "
            if(item)
                item = '<div class=\'ib-suffix-container\'>" . $this->getPrefixString() . "' + item + '" . $this->getSuffixString() . "</div>';
        ";

		return null;
	}

	public function getPrefixString()
	{
		if (! $prefix = $this->getPrefix())
			return null;

		return "<div class=\'ib-prefix\'><div>" . $prefix . "</div></div>";
	}

	public function getSuffixString()
	{
		if (! $suffix = $this->getSuffix())
			return null;

		return "<div class=\'ib-suffix\'><div>" . $suffix . "</div></div>";
	}

	public function getCustomColumnDefSingleSearchResult()
	{
		return "
			item = item[1];
        ";
	}

	public function getCustomColumnDefSingleSortResult()
	{
		return "
			item = item[1];
        ";
	}

	public function getCustomColumnDefSingleResultEditor()
	{
		return "

		item = item[1];

		";
	}

	protected function substituteUrlParameter()
	{
		return "
			let url = '" . $this->getEditorUpdateUrl() . "';
			url = url.replace('" . config("datatables.replace_model_id_string") . "', item[0]);

			if(item[1] === null)
				item[1] = '';

		";
	}

	public function getEditorUpdateUrl() : string
	{
		if ($customRouteName = $this->getCustomUpdateRouteName())
			return $this->getCustomUpdateUrl();

		if (! $this->requireElement())
			return $this->element::getDatatableEditorUrl();

		if ($placeholder = $this->getPlaceholderElement())
			if (method_exists($placeholder, 'getEditorUpdateUrl'))
				return $placeholder->getEditorUpdateUrl();

		$routeName = $this->getUpdateRouteName();
		$parameters = $this->getUpdateParameters();

		try
		{
			return route($routeName, $parameters);
		}
		catch (Exception $e)
		{
			if ($this->debug())
				ddd($e->getMessage() . '. ------ Also check pluralization for ' . $routeName . ' and ' . json_encode($parameters));

			throw new Exception($e->getMessage() . '. ------ Also check pluralization for ' . $routeName . ' and ' . json_encode($parameters));
		}
	}


	// <div class="uk-width-1-2@s input-group">
	//        <input class="uk-input" type="text" placeholder="50">
	//      <div class="input-group-append">
	//        <div class="input-group-text">
	//          @email
	//        </div>
	//      </div>
	//    </div>

	public function getCustomUpdateRouteName()
	{
		return $this->customUpdateRouteName;
	}

	public function getCustomUpdateUrl()
	{
		$routeName = $this->getCustomUpdateRouteName();
		$parameters = $this->getUpdateParameters();

		return route($routeName, $parameters);
	}

	public function getUpdateParameters()
	{
		$routeElementClassName = $this->getRouteElementClassName();

		$routeElementParameterName = $this->getRouteElementParameterName($routeElementClassName);

		return [
			$routeElementParameterName => config("datatables.replace_model_id_string")
		];
	}

	public function getRouteElementClassName()
	{
		if ($this->pluralModelClass ?? false)
			return $this->pluralModelClass;

		$element = $this->element ?? $this->getPlaceholderElement();

		if (method_exists($element, 'getRouteBasename'))
			return $element->getRouteBasename();

		$this->pluralModelClass = Str::plural(
			lcfirst(
				class_basename($this->element ?? $this->getPlaceholderElement())
			)
		);

		return $this->getRouteElementClassName();
	}

	public function getRouteElementParameterName($routeElementClassName)
	{
		if ($placeholderElement = $this->getPlaceholderElement())
			return Str::camel(class_basename($placeholderElement));

		return Str::singular($routeElementClassName);
	}

	public function getUpdateRouteName()
	{
		$routeElementClassName = $this->getRouteElementClassName();

		return $routeElementClassName . '.update';
	}
}