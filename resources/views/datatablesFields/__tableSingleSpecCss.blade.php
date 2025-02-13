
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
	#{{ $table->getId() }} tbody tr > *:nth-child({{ $loop->index - 1 }})
	{
		{{ $alignmentString }}
	}
	@endif

    @if(! empty($field->width))

#{{ $table->getId() }} th.{{ $field->getHtmlClassForCss() }},
#{{ $table->getId() }} td.{{ $field->getHtmlClassForCss() }}
{
	/*{!! json_encode($field->name) !!}*/
    min-width: {{ $field->getWidth() }}!important;
    max-width: {{ $field->getWidth() }}!important;
    width: {{ $field->getWidth() }}!important;
}
    @endif

@endforeach

</style>
