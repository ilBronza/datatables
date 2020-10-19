@foreach($value as $key => $item)
 	@include('datatables::datatables.fields.json._' . $field->subView['view'], [
		'value' => $item,
		'field' => (object) $field->subView
		])

	@if(! $loop->last)
	{!! $field->separator ?? '-' !!}
	@endif
@endforeach