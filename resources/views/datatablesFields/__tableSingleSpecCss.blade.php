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
		#{{ $table->getId() }} th.{{ $field->getHtmlClassForCss() }} @if($field->isEditor()) input @endif,
       	#{{ $table->getId() }} td.{{ $field->getHtmlClassForCss() }} @if($field->isEditor()) input @endif
	{
		{{ $alignmentString }}
	}
	@endif

/*lemme lemme*/
/*{!! json_encode($field->name) !!}*/

    @if(! empty($field->width))

#{{ $table->getId() }} th.{{ $field->getHtmlClassForCss() }},
#{{ $table->getId() }} td.{{ $field->getHtmlClassForCss() }}
{
		/*lemme lemme*/
	/*{!! json_encode($field->name) !!}*/
    min-width: {{ $field->getWidth() }}!important;
    max-width: {{ $field->getWidth() }}!important;
    width: {{ $field->getWidth() }}!important;
}
    @endif

@endforeach

</style>
