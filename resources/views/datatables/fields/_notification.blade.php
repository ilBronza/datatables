@php
	$notificationType = explode("\\", $value->type);
	$viewName = 'notifications.teasers.' . array_pop($notificationType);
@endphp

@if(view()->exists($viewName))
	@include($viewName, ['notification' => $value])
@else
{{ trans('errors.viewMissing', ['view' => $viewName]) }}
@endif