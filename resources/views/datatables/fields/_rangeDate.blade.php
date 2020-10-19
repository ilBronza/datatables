@if($value)
{{ date('Y-m-d', strtotime($value)) }}
@else
<span class="uk-text-danger">@lang('generals.missing')</span>
@endif