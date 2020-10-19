<!-- START \views\tables\fields\_courseclassDateConfirmation.blade.php -->

@php
	
	$neededMinimumCourseHours = $element->course->getMinimumNeededHours();

	$workers = $value->dates->pluck('workers')->flatten();
	$total = $workers->count();
	$toManage = $workers->where('pivot.confirmed', '!=', null)->count();

@endphp

<a href="{{ $value->getDateConfirmationUrl() }}">
	<span uk-icon="calendar"></span>
	@if($total > $toManage)
		<span class="uk-text-warning">@lang('courseclasses.datesToManage', ['tomanage' => $toManage, 'total' => $total])</span>
	@else
		<span class="uk-text-success">@lang('courseclasses.managed')</span>
	@endif
</a>
@if($value->areConfirmedHoursReachedByCompany($company))
	<span class="uk-text-success">@lang('courseclasses.minimumHoursReached')</span>
@else
	<span class="uk-text-warning">@lang('courseclasses.minimumHoursNotReached')</span>
@endif
<!-- END \views\tables\fields\_courseclassDateConfirmation.blade.php -->