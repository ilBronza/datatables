{{-- @php
$programmed = $value->getProgrammedHoursAmount();
@endphp

<a uk-tooltip="" title="@lang('courseclasses.manageWorkers')" href="{{ $value->getManageWorkersUrl() }}"><span uk-icon="user"></span>
	<strong class="{{ ($value->course->hours == $programmed)? 'uk-text-success' : 'uk-text-warning' }}">

		{{ $value->workers->count() }}

		@if($value->min_workers > 0)
		 / {{ $value->min_workers }}
		@endif

		@lang('courseclasses.manageWorkers', [
			'total' => $value->workers->count()
		])</strong>
</a>

 --}}