@if(count($table->options))

    @foreach($table->options as $name => $value)

    	@if($name == 'order')
    	"{{ $name }}" : 
    	[
    		@foreach($value as $index => $order)

			{!! json_encode($order) !!}@if(! $loop->last),
    			@endif
    		@endforeach
		
		],
    	@else
        "{{ $name }}" : {!! json_encode($value) !!},
        @endif

    @endforeach

@endif
