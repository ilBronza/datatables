@if($value->isImportant())
<span class="uk-text-danger">{{ $value->alias }}</span>
@else
{{ $value->alias }}
@endif
