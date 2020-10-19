@foreach($field->subfields as $translation)
	@lang($translation . '.' . $value)
@endforeach
