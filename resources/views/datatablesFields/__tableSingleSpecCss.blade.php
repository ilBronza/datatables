
<style type="text/css">

@foreach($table->getFields() as $field)
    @if(! empty($field->width))
#{{ $table->getId() }} th.{{ $field->getHtmlClass() }}
{
    width: {{ $field->width }};
    max-width: {{ $field->width }};
}
    @endif

@endforeach

</style>
