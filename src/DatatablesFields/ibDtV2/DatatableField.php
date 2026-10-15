<?php

namespace IlBronza\Datatables\DatatablesFields\ibDtV2;

use IlBronza\Datatables\Contracts\DatatableFieldContract;
use IlBronza\Datatables\Datatables;
use IlBronza\Datatables\DatatablesFields\ibDtV2\Traits\ColumnOptionsTrait;
use IlBronza\Datatables\DatatablesFields\ibDtV2\Traits\FieldIdentityTrait;
use IlBronza\Datatables\DatatablesFields\ibDtV2\Traits\FieldPermissionsTrait;
use IlBronza\Datatables\DatatablesFields\ibDtV2\Traits\HeaderTrait;
use IlBronza\Datatables\DatatablesFields\ibDtV2\Traits\HtmlAttributesTrait;
use IlBronza\Datatables\DatatablesFields\ibDtV2\Traits\ModelBindingTrait;
use IlBronza\Datatables\DatatablesFields\ibDtV2\Traits\RendererTrait;
use IlBronza\Datatables\DatatablesFields\ibDtV2\Traits\TableIntegrationTrait;
use InvalidArgumentException;
use ReflectionProperty;

abstract class DatatableField implements DatatableFieldContract
{
	use FieldIdentityTrait;
	use FieldPermissionsTrait;
	use ColumnOptionsTrait;
	use HeaderTrait;
	use ModelBindingTrait;
	use TableIntegrationTrait;
	use HtmlAttributesTrait;
	use RendererTrait;

	public function __construct(string $name, array $parameters = [], ?int $index = null, ?DatatableFieldContract $parent = null, ?Datatables $table = null)
	{
		$this->name = $name;
		$this->id = 'ib-dt-' . bin2hex(random_bytes(8));
		$this->index = $index ?? 0;
		$this->parent = $parent;
		$this->table = $table;
		$this->configure($parameters);
	}

	/** Only parameters declared by this V2 field's traits are accepted. */
	protected function configure(array $parameters) : void
	{
		foreach ($parameters as $name => $value)
		{
			if ($name === 'view')
				$name = 'type';

			if (! is_string($name) || ! property_exists($this, $name)
				|| ! (new ReflectionProperty($this, $name))->isPublic()
				|| in_array($name, ['name', 'id', 'index', 'table', 'parent', 'fieldsGroup', 'element'], true))
				throw new InvalidArgumentException('Unsupported V2 parameter "' . $name . '" for field "' . $this->name . '".');

			$this->{$name} = $value;
		}
	}
}
