@if($table->hasSummary())

<tr class="summary" data-dt-order="disable" style="display: none;">
    @foreach($table->getFields() as $field)
        <th
            class="summary{{ $field->getIndex() }} @if($field->getSummaryType()) ib-dt-summary-scope @endif"
            data-name="{{ $field->getFieldName() }}"
            data-column="{{ $field->getIndex() }}"
            @if($field->getSummaryType())
                data-summary-label="{{ __('datatables::datatables.summarySelectedRows') }}"
            @endif
        ></th>
    @endforeach
</tr>

<tr class="inlinesearchsummary" data-dt-order="disable" style="display: none;">
    @foreach($table->getFields() as $field)
    <th
        class="summary{{ $field->getIndex() }} @if($field->getSummaryType()) ib-dt-summary-scope @endif"
        data-name="{{ $field->getFieldName() }}"
        data-column="{{ $field->getIndex() }}"
        @if($field->getSummaryType())
            data-summary-label="{{ __('datatables::datatables.summaryFilteredRows') }}"
        @endif
    ></th>
    @endforeach
</tr>

@endif
