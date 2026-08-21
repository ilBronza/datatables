<?php

namespace IlBronza\Datatables\DatatablesFields;

use LogicException;

use function route;

/**
 * A lightweight cell whose display value is loaded in one request per column.
 *
 * The column contains only a render target. Row identifiers come from the
 * DataTables row ID; endpoint and renderer live on the column header.
 */
class DatatableFieldAsync extends DatatableField
{
	public $sortable = false;
	public $filterable = false;

	/** Already resolved endpoint URL (for example route('orders.alerts')). */
	public ?string $route = null;

	/** Optional Laravel route name, resolved with $routeParameters. */
	public ?string $routeName = null;
	public array $routeParameters = [];

	/** Kept for backwards compatibility with the first prototype (route name). */
	public ?string $url = null;

	/** Name registered in window.ibAsyncRenderers. */
	public string $renderer = 'text';

	/** Reload on every draw by default; opt in only for immutable payloads. */
	public bool $cache = false;

	public function getAsyncRoute() : string
	{
		if ($this->route)
			return $this->route;

		if ($this->routeName)
			return route($this->routeName, $this->routeParameters);

		if ($this->url)
			return route($this->url, $this->routeParameters);

		throw new LogicException("Il campo async [{$this->name}] richiede route oppure routeName");
	}

	public function getRenderer() : string
	{
		return $this->renderer ?: 'text';
	}

	/** Header data is the public contract consumed by the draw listener. */
	public function parseFieldSpecificHeaderData()
	{
		$this->setHeaderDataAttribute('async-route', $this->getAsyncRoute());
		$this->setHeaderDataAttribute('async-renderer', $this->getRenderer());
		$this->setHeaderDataAttribute('async-cache', $this->cache ? '1' : '0');
	}

	/**
	 * Row identifiers already belong to DataTables/the tr node. The async
	 * column must not repeat them in its own dataset.
	 */
	public function transformValue($value)
	{
		return null;
	}

	/** The async renderer owns the whole td, so no inner wrapper is required. */
	public function getCustomColumnDefSingleResult()
	{
		return "item = '';";
	}

	public function getCustomColumnDefSingleResultExport()
	{
		return "item = '';";
	}

	public function getCustomColumnDefSingleSearchResult()
	{
		return "item = '';";
	}

	public function getCustomColumnDefSingleSortResult()
	{
		return "item = '';";
	}
}
