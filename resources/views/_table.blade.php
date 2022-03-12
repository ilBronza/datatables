@include('datatables::datatablesFields._tableSingleSpec')

@if(! request()->input('justTable', false))
    @include('datatables::__extraViews', ['position' => 'top'])
@endif

@if($table->canHideColumns())

<script type="text/javascript">
jQuery(document).ready(function($)
{
    $('a.toggle-vis').on('click', function (e) {
        var that = this;
        e.preventDefault();

        var table = $('#{{ $table->getId() }}').DataTable();

        var column = table.column($(that).data('column'));

        // Toggle the visibility
        column.visible( ! column.visible() );

        if($(that).data('show') == 1)
        {
            $(that).data('show', 0);
            $(that).removeClass('uk-text-bold');
        }
        else
        {
            $(that).data('show', 1);
            $(that).addClass('uk-text-bold');
        }
    } );


});
    
</script>



<div class="uk-button-group">
    <button class="uk-button uk-button-default">Fields visibility</button>
    <div class="uk-inline">
        <button class="uk-button uk-button-default" type="button"><span uk-icon="icon:  triangle-down"></span></button>
        <div uk-dropdown="mode: click; boundary: ! .uk-button-group; boundary-align: true;">
            <ul class="uk-nav uk-dropdown-nav">
                @foreach($table->getFields() as $field)
                <li>
                    <a
                        href="javascript:void(0)"
                        class="toggle-vis uk-text-bold"
                        data-column="{{ $field->getIndex() }}"
                        data-show="1"
                        style="color: black;"
                        >
                        {{ $field->getTranslatedName() }}
                    </a>
                </li>
                @endforeach
            </ul>
        </div>
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
        data-relation={{ $table->getRelationName() }}
        @endif

        @if($table->drawOnFieldsEvents())
        data-filter-draw-on-events="true"
        @endif

        @if($table->hasSummary())
        data-summary="true"
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
                    uk-tooltip="{{ $field->getTranslatedName() }}"
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
