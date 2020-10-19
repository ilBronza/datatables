@if(count($table->topViews))

@foreach($table->topViews as $data)
	@include($view = key($data), $data[$view])
@endforeach

@endif