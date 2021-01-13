@foreach($table->getFields() as $field)
    @if($field->hasRangeFilter())
    {!! $field->getRangeFilterJavascriptPlugin($table->getId()) !!}
    @endif

@endforeach
