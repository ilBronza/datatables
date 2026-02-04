@if($table->hasSummary())

<tr class="summary" style="display: none;">
    @foreach($table->getFields() as $field)
        <th class="summary{{ $field->getIndex() }}" data-name="{{ $field->getFieldName() }}" data-column="{{ $field->getIndex() }}"></th>
    @endforeach
</tr>

<tr class="inlinesearchsummary" style="display: none;">
    @foreach($table->getFields() as $field)
    <th class="summary{{ $field->getIndex() }}" data-name="{{ $field->getFieldName() }}" data-column="{{ $field->getIndex() }}"></th>
    @endforeach
</tr>

@endif