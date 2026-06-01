@if($summary = $field->getSummaryType())
data-summary="{{ $summary }}"
@endif
@foreach($field->getSummaryDataAttributes() as $attr => $value)
data-{{ $attr }}="{{ $value }}"
@endforeach
