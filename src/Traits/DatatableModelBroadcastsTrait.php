<?php

namespace IlBronza\Datatables\Traits;

use Illuminate\Support\Str;

trait DatatableModelBroadcastsTrait
{
	public ?bool $listenToModelBroadcasts = null;
	public ?string $modelBroadcastChannel = null;
	public ?string $refreshTableBroadcastChannel = null;
	public ?string $modelBroadcastCreatedFetchUrl = null;
	public ?string $modelBroadcastCreatedFetchRequestHook = null;

	public function setListenToModelBroadcasts(bool $listen = true) : static
	{
		$this->listenToModelBroadcasts = $listen;

		return $this;
	}

	public function shouldListenToModelBroadcasts() : bool
	{
		if (is_null($this->listenToModelBroadcasts))
			return (bool) config('datatables.modelBroadcasts.enabled', false);

		return $this->listenToModelBroadcasts;
	}

	public function setModelBroadcastChannel(?string $channel) : static
	{
		$this->modelBroadcastChannel = $channel;

		return $this;
	}

	public function getModelBroadcastChannel() : ?string
	{
		if ((! $this->shouldListenToModelBroadcasts()) || (! $this->modelClass))
			return null;

		return $this->modelBroadcastChannel
			?? 'channel.crud-events.models.' . Str::kebab(class_basename($this->modelClass));
	}

	public function setRefreshTableBroadcastChannel(?string $channel) : static
	{
		$this->refreshTableBroadcastChannel = $channel;

		return $this;
	}

	public function getRefreshTableBroadcastChannel() : ?string
	{
		return $this->refreshTableBroadcastChannel
			?? 'channel.tables.' . Str::kebab($this->getName()) . '.refresh';
	}

	public function setModelBroadcastCreatedFetchUrl(?string $url) : static
	{
		$this->modelBroadcastCreatedFetchUrl = $url;

		return $this;
	}

	public function getModelBroadcastCreatedFetchUrl() : ?string
	{
		return $this->modelBroadcastCreatedFetchUrl;
	}

	public function setModelBroadcastCreatedFetchRequestHook(?string $hook) : static
	{
		$this->modelBroadcastCreatedFetchRequestHook = $hook;

		return $this;
	}

	public function getModelBroadcastCreatedFetchRequestHook() : ?string
	{
		return $this->modelBroadcastCreatedFetchRequestHook;
	}
}
