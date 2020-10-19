@php
$bannedName = str_replace("table", "banned", $tableName)
@endphp

@if(isset($$bannedName->{$element->course->common_alias}))
	@if($element->created_at < $$bannedName->{$element->course->common_alias})
	<strong class="uk-text-danger">@lang('courses.banned')</strong>
	@endif
@endif