{{-- @php
$programmed = $value->getProgrammedHoursAmount();
@endphp

<a uk-tooltip="" title="@lang('courseclasses.manageDates')" href="{{ $value->getManageDatesUrl() }}"><span uk-icon="calendar"></span>
	<strong class="{{ ($value->course->hours == $programmed)? 'uk-text-success' : 'uk-text-warning' }}">
		@lang('courseclasses.programmedHoursinCountDates', [
			'programmed' => $programmed,
			'total' => $value->course->hours,
			'dates' => $value->dates->count()
		])</strong>
</a>

 --}}