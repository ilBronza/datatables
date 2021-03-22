
<style type="text/css">

@foreach($table->getFields() as $field)
    @if(! empty($field->width))
#{{ $table->getId() }} th.{{ $field->getHtmlClassForCss() }}
{
	/*{!! json_encode($field) !!}*/
    width: {{ $field->width }};
    max-width: {{ $field->width }};
    min-width: {{ $field->width }};
}
    @endif

@endforeach

</style>
