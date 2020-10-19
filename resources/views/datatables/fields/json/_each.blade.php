@foreach($value as $key => $item)
 	@include('datatables::datatables.fields.json._' . $field->subView['view'], [
		'value' => (isset($field->parameter))? $item->{$field->parameter} : $item,
		'field' => (object) $field->subView,
		])

	@if(! $loop->last)
	{!! $field->separator ?? '-' !!}
	@endif
@endforeach