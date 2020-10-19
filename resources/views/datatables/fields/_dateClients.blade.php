<!-- START \views\tables\fields\_dateClients.blade.php -->

@if($value)
	@if($value instanceof \Carbon\Carbon)
		@if($value->getTimestamp() == 0)
			<strong class="uk-text-danger">@lang('messages.urgent')</strong>
		@elseif($value->getTimestamp() > \Carbon\Carbon::now()->addYears(50)->getTimestamp())
			\
		@else
			<span>{{ date(trans('dates.search'), strtotime($value)) }}</span>
			{{ $value->format(trans('dates.human')) }}
		@endif
	@else
	{{ $value }}
	@endif
@endif

<!-- END \views\tables\fields\_dateClients.blade.php -->
