@if($arrayName = ($field->array ?? false))
	@foreach($value->pluck($field->property)->unique() as $_value)
		{{ $$arrayName[$_value] }}
	@endforeach
@else
	@foreach($value->pluck($field->property)->unique() as $_value)
		{{ $_value }}
	@endforeach
@endif