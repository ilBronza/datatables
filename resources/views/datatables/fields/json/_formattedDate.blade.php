@if($value)
	{{ $value->format(__('dates.' . $field->format)) }}
@endif