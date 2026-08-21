@if($table->hasSummary())

@php($summaryLabelField = $table->getFields()->first(fn ($field) => $field->getSummaryType()))
@php($summaryLabelFieldIndex = $summaryLabelField ? $summaryLabelField->getIndex() : null)

<tr class="summary" style="display: none;">
    @foreach($table->getFields() as $field)
        <th
            class="summary{{ $field->getIndex() }} @if($field->getIndex() === $summaryLabelFieldIndex) ib-dt-summary-scope @endif"
            data-name="{{ $field->getFieldName() }}"
            data-column="{{ $field->getIndex() }}"
            @if($field->getIndex() === $summaryLabelFieldIndex)
                data-summary-label="{{ __('datatables::datatables.summarySelectedRows') }}"
                aria-label="{{ __('datatables::datatables.summarySelectedRows') }}"
            @endif
        ></th>
    @endforeach
</tr>

<tr class="inlinesearchsummary" style="display: none;">
    @foreach($table->getFields() as $field)
    <th
        class="summary{{ $field->getIndex() }} @if($field->getIndex() === $summaryLabelFieldIndex) ib-dt-summary-scope @endif"
        data-name="{{ $field->getFieldName() }}"
        data-column="{{ $field->getIndex() }}"
        @if($field->getIndex() === $summaryLabelFieldIndex)
            data-summary-label="{{ __('datatables::datatables.summaryFilteredRows') }}"
            aria-label="{{ __('datatables::datatables.summaryFilteredRows') }}"
        @endif
    ></th>
    @endforeach
</tr>

@endif
