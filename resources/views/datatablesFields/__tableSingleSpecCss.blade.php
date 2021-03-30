
<style type="text/css">

@foreach($table->getFields() as $field)
    @if(! empty($field->width))
#{{ $table->getId() }} th.{{ $field->getHtmlClassForCss() }}
{
	/*{!! json_encode($field->name) !!}*/
    max-width: {{ $field->width }}!important;
}
    @endif

@endforeach

</style>
