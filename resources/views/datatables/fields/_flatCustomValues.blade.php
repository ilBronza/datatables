@php
	$parameterName = $column->label;
@endphp

{{ $element->$parameterName ?? ''}}
