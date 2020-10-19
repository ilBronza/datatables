{{{ $value }}}{{-- @php
$arrayValues = json_decode($value);
@endphp

@if(is_array($arrayValues))
	@foreach($arrayValues as $_value)
		{{ $_value }}
		@if(! $loop->last)
		-
		@endif
	@endforeach
@endif --}}