
<style type="text/css">

@foreach($table->getFields() as $field)
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
