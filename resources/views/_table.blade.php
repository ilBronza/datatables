{{-- START SOLO PER IDEALPACK, RISOLVERE --}}
@section('pageTitle')
    @if($caption = $table->getCaption())
    <span class="uk-h3">{{ $caption }}</span>
    @endif
@endsection
{{-- END SOLO PER IDEALPACK, RISOLVERE --}}

@include('datatables::datatablesFields._tableSingleSpec')

@if(! request()->input('justTable', false))
    @include('datatables::__extraViews', ['position' => 'top'])
@endif

    <table
        id="{{ $table->getId() }}"

        @if($table->isAjaxTable())
        data-url="{{ $table->getUrl() }}"
        data-cachedtablekey="{{ $table->getCachedTableKey() }}"
        @endif

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
                    data-camelName="{{ $field->getCamelName() }}"
                    data-column="{{ $field->getIndex() }}"

                    @foreach($field->getHeaderData() as $data => $value)
                    data-{{ $data }}="{{ $value }}"
                    @endforeach

                    @if($field->hasFieldOperations())
                    data-fieldOperations="{{ json_encode($field->getFieldOperations()) }}"
                    @endif

                    @if($field->getFilteredTable())
                    data-filteredTable="{{ $field->getFilteredTable() }}"
                    @endif
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
    </table>


@if(! request()->input('justTable', false))
    @include('datatables::__extraViews', ['position' => 'bottom'])
@endif
