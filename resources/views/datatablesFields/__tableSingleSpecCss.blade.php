<style type="text/css">

	@foreach($table->getFields() as $index => $field)

		/*colonna indice {{ $loop->index }}*/

		@if($field->hasCss())
	#{{ $table->getId() }} tbody tr > *:nth-child({{ $loop->index - 1 }}) {
		@foreach($field->getCssRules() as $cssRule)
    	{{ $cssRule }};
	    @endforeach
    }
	   @endif

	@if($alignmentString = $field->getAlignmentCssString())
		{{-- #{{ $table->getId() }} th.{{ $field->getHtmlClassForCss() }} @if($field->isEditor()) input @endif, --}}
       	#{{ $table->getId() }} td.{{ $field->getHtmlClassForCss() }} @if($field->isEditor()) input @endif
	{
		{{ $alignmentString }}
	}
	@endif

/*lemme lemme*/
/*{!! json_encode($field->name) !!}*/

    @if((! empty($width = $field->width))&&($width != 'auto'))

		#{{ $table->getId() }} th.{{ $field->getHtmlClassForCss() }},
		#{{ $table->getId() }} td.{{ $field->getHtmlClassForCss() }},
		#{{ $table->getId() }}_wrapper th.{{ $field->getHtmlClassForCss() }},
		#{{ $table->getId() }}_wrapper td.{{ $field->getHtmlClassForCss() }}
		{
			min-width: {{ $width }}!important;
			max-width: {{ $width }}!important;
			width: {{ $width }}!important;
		}
    @endif

@endforeach

</style>
