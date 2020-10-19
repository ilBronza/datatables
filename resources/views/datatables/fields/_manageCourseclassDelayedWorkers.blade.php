<a uk-tooltip="" title="@lang('courseclasses.manageWorkers')" href="{{ $value->getManageDelayedWorkersUrl() }}"><span uk-icon="user"></span>
	<strong class="">

		@lang('courseclasses.manageDelayedWorkers', [
			'total' => $value->delayedWorkers()->count()
		])</strong>
</a>

