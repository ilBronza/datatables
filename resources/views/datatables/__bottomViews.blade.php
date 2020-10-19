@if(count($table->bottomViews))

@foreach($table->bottomViews as $data)
	@include($view = key($data), $data[$view])
@endforeach

@endif