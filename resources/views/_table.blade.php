@include('datatables::datatablesFields._tableSingleSpec')

@if(! request()->input('justTable', false))
    @include('datatables::__extraViews', ['position' => 'top'])
@endif

@if($table->canHideColumns())

<button hidden class="colvisbutton" id="offcanvastogglefieldsbutton{{ $table->getId() }}" uk-toggle="target: #offcanvastogglefields{{ $table->getId() }}">Fields visibility</button>

<div id="offcanvastogglefields{{ $table->getId() }}" uk-offcanvas>
    <div class="uk-offcanvas-bar">

        <button class="uk-offcanvas-close" type="button" uk-close></button>

        <ul id="togglefields{{ $table->getId() }}" class="uk-nav uk-dropdown-nav toggle-vis-container table{{ $table->getId() }}" data-tableid="{{ $table->getId() }}">
            @foreach($table->getFields() as $field)
            <li>
                <a
                    href="javascript:void(0)"
                    class="toggle-vis @if($field->isVisible()) uk-text-bold @endif"
                    data-column="{{ $field->getIndex() }}"
                    data-name="{{ $field->getFieldName() }}"
                    data-visibility="{{ ($field->isVisible() ? 1 : 0) }}"
                    style="color: black;"
                    >
                    {{ $field->getTranslatedName() }}
                </a>
            </li>
            @endforeach
        </ul>
    </div>
</div>

@endif

    <table
        id="{{ $table->getId() }}"

        @if($table->isAjaxTable())
        data-url="{{ $table->getUrl() }}"
        data-cachedtablekey="{{ $table->getCachedTableKey() }}"
        @endif

        @if($table->getRelationName())
        data-relation="{{ $table->getRelationName() }}"
        @endif

        @if($table->drawOnFieldsEvents())
        data-filter-draw-on-events="true"
        @endif

        @if($table->hasSummary())
        data-summary="true"
        @endif

        @if($table->usesColumnDisplay())
        data-columndisplayroute="{{ $table->getColumnDisplayRoute() }}"
        @endif

        {!! $table->getDomStickynessDataAttribute() !!}

        class="wannabedatatable uk-table {{ $table->getStripeClass() }} datatable {{ $table->getName() }}"
        style="width:100%;"
        >
        <thead class="sectionheader">
            <tr class="columns">
                @foreach($table->getFields() as $field)
                <th
                    @if(config('datatables.useTooltips'))
                    uk-tooltip="offset: 20; title: {{ $field->getTranslatedName() }}"
                    @endif

                    class="{{ $field->getHeaderHtmlClasses() }}"

                    data-ajaxExtraData="{{ $field->getJsonAjaxExtraData() }}"

                    data-showDuplicates="{{ $field->hasDoubler() }}"
                    data-range-filter="{{ $field->hasRangeFilter() }}"
                    data-filter-type="{{ $field->getFilterType() }}"
                    data-filterable="{{ $field->isFilterable() }}"
                    data-filter-events="{{ $field->getJqueryFilterEventsString() }}"
                    data-filter-draw-on-events="{{ $field->canDrawTable() }}"
                    data-filter-draw-on-keyup="{{ $field->canDrawKeyup() }}"
                    data-name="{{ $field->getFieldName() }}"
                    data-label="{{ $field->getTranslatedName() }}"                    
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
