@php
	$methodName = $column->subfields[0];
@endphp

{{ $value->$methodName() }}