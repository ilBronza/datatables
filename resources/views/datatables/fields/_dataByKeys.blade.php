@foreach($value as $_value)
{{ $dataByKeys[$_value->data_id ?? null]->alias ?? '-' }}
@endforeach