
<style type="text/css">

@foreach($table->getFields() as $field)
    @if(! empty($field->width))
#{{ $table->getId() }} th.{{ $field->getHtmlClassForCss() }}
{
	/*{!! json_encode($field->name) !!}*/
    min-width: {{ $field->width }}!important;
    max-width: {{ $field->width }}!important;
    width: {{ $field->width }}!important;
}
    @endif

@endforeach

</style>
