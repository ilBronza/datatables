@section('pageTitle')
    @if($caption = $table->getCaption())
    <span class="uk-h3">{{ $caption }}</span>
    @endif
@endsection

@include('datatables::datatablesFields._tableSingleSpec')

@include('datatables::__extraViews', ['position' => 'top'])

    <table
        id="{{ $table->getId() }}"
        data-url="{{ $table->getUrl() }}"
        data-cachedtablekey="{{ $table->getCachedTableKey() }}"

        @if($table->hasSummary())
        data-summary="true"
        @endif

        class="wannabedatatable uk-table {{ $table->getStripeClass() }} datatable {{ $table->getName() }}"
        style="width:100%;"
        >
        <thead class="sectionheader">
            <tr class="columns">
                @foreach($table->getFields() as $field)
                <th
                    uk-tooltip="{{ $field->getTranslatedName() }}"
                    class="{{ $field->getHeaderHtmlClasses() }}"

                    data-range="{{ $field->hasRangeFilter() }}"
                    data-filter="{{ $field->getFilterType() }}"
                    data-name="{{ $field->getFieldName() }}"
                    data-column="{{ $field->getIndex() }}"
                    >
                    {{ $field->renderHeader() }}
                </th>
                @endforeach
            </tr>

            @if($table->hasSummary())

            <tr class="summary" style="display: none;">
                @foreach($table->getFields() as $field)
                <th
                    class="summary{{ $field->getIndex() }}"
                    data-name="{{ $field->getFieldName() }}"
                    data-column="{{ $field->getIndex() }}"
                    >
                </th>
                @endforeach
            </tr>

                @if($table->hasInlineSearch())

            <tr class="inlinesearchsummary" style="display: none;">
                @foreach($table->getFields() as $field)
                <th
                    class="summary{{ $field->getIndex() }}"
                    data-name="{{ $field->getFieldName() }}"
                    data-column="{{ $field->getIndex() }}"
                    >
                </th>
                @endforeach
            </tr>

                @endif

            @endif


        </thead>
{{--         <tfoot>
            <tr>
                @foreach($table->getFields() as $field)
                <th>{{ $field->renderHeader() }}</th>
                @endforeach
            </tr>
        </tfoot>
 --}}    </table>


@include('datatables::__extraViews', ['position' => 'bottom'])

