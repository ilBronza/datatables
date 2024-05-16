<script type="text/javascript">

    @if($table->isArrayTable())
    window.{{ $table->getId() }}dataset = {!! json_encode($tableSourceData) !!};
    @endif

    window.{{ $table->getId() }}FilteredSelected = false;

    window.{{ $table->getId() }}rowReorder = @isset($table->dragAndDrop) {
   
            @if(isset($table->dragAndDrop->selector))
            selector: '{{ $table->dragAndDrop->selector }}',
            dataSrc: {{ $table->dragAndDrop->dataSrc ?? 1 }}
            @endif

        } @else false @endisset ;

    window.{{ $table->getId() }}options =  { 

        @if($table->hasFixedTableHeader())
        fixedHeader: true,
        @endif

        //SCROLLX genera parecchi problemi con il grab della tabella HTML se attivo. non trova più i data etc
        //TODO
        {{-- scrollX: {{ $table->canScrollX() ? 'true' : 'false' }}, --}}

        @if($caption = $table->getCaption())
        caption: "{{ $caption }}",
        @endif
        keys : true,
        language : {
            lengthMenu: "{!! __('datatables::datatables.show_MENU_entries') !!}",
            info : "{!! __('datatables::datatables.showing_START_to_END_of_TOTAL_entries') !!}",
            search : "{!! __('datatables::datatables.search') !!}",
            searchPlaceholder : "{!! __('datatables::datatables.searchPlaceholder') !!}",
            paginate: {
                    first : "{!! __('datatables::datatables.first') !!}",
                    last : "{!! __('datatables::datatables.last') !!}",
                    next : "{!! __('datatables::datatables.next') !!}",
                    previous : "{!! __('datatables::datatables.previous') !!}",
                },
        },

        @if($dom = $table->getCustomDom())
        dom: '{!! $dom !!}', //sgarruio
        @endif

        @if($table->isAjaxTable())
        //ajaxxalo
        ajax: {
            url: window.addParameterToURL(window.addParameterToURL("{{ $table->getUrl() }}", 'cachedtablekey', "{{ $table->getCachedTableKey() }}"), 'model', '{{ $table->getRelationName() }}'),
            dataSrc: function(json)
            {
                let data = window.transformDataBySummaryExistence("{{ $table->getId() }}", "{{ $table->hasSummary() }}", json);

                return data;
            }
        },
        {{-- @elseif($table->isArrayTable()) --}}
        //arrayalo
        @else
            data: window.{{ $table->getId() }}dataset,
        @endif

        @if($table->hasSelectRowCheckboxes())
        select: {
            style:    'multi',
            selector: 'td:first-child'
        },
        @endif

        @if($scripts = $table->getCreatedRowScripts())

        //uash uash
        createdRow: function( row, data, dataIndex )
        {
            @foreach ($scripts as $script)
            {!! $script !!}
            @endforeach
        },        
        @endif

        pageLength: {{ $table->getPageLength() }},

        @if($table->getRowIdIndex() !== null)
        rowId: {{ $table->getRowIdIndex() }},
        @endif

            @foreach($table->options as $name => $value)
                @if($name == 'order')
        "{{ $name }}" : 
        [
            //{!! json_encode($value) !!}
            @foreach($value as $index => $order)
            {!! json_encode($order) !!}@if(! $loop->last),@endif
            @endforeach
        
        ],
                @else
        "{{ $name }}" : {!! json_encode($value) !!},
                @endif
            @endforeach
        };

    // window.{{ $table->getId() }}options = @if(count($table->options)) {
    //     @foreach($table->options as $name => $value)

    //     @if($name == 'order')
    //     "{{ $name }}" : 
    //     [
    //         @foreach($value as $index => $order)

    //         {!! json_encode($order) !!}@if(! $loop->last),
    //             @endif
    //         @endforeach
        
    //     ],
    //     @else
    //     "{{ $name }}" : {!! json_encode($value) !!},
    //     @endif

    // @endforeach
    // }
    // @endif ;

    window.{{ $table->getId() }}columnDefs = [
        {
            "targets"  : 'no-sort',
            "orderable": false            
        }@if(count($table->columnDefs)||(count($table->customColumnDefs))),

        @if($table->hasSelectRowCheckboxes())
        {
           orderable: false,
           className: 'select-checkbox',
           targets: 0,
        },
        @endif
    @foreach ($table->columnDefs as $type => $element)
        @foreach($element->values as $key => $value)
        @if($key == '')
        {"{{ $type }}": false, "targets": {{ json_encode($value) }}},
        @elseif($key == 1)
        {"{{ $type }}": true, "targets": {{ json_encode($value) }}},
        @elseif($key == 'dom-text-numeric')
        {"{{ $type }}": "{{ $key }}", "sType" : "numeric", "targets": {{ json_encode($value) }}},
        @else
        {"{{ $type }}": "{{ $key }}", "targets": {{ json_encode($value) }}},
        @endif
        @endforeach
    @endforeach

    //count columnDefs = {{ count($table->customColumnDefs) }}

    @foreach ($table->customColumnDefs as $customColumnDef)

    //INDEX LOOP {{ $loop->index }}

        {!! $customColumnDef !!},
    @endforeach
    @endif ];

    window.{{ $table->getId() }}buttons = @if(count($buttons = $table->getButtons())||(count(1))) [

        'fieldsVisibility',

        @if($table->hasSearchButton())
        'search',
        @endif
        'reload',

        @if($table->hasSelectFilteredButton())
        {
            text: 'selectFiltered',
            action : function ( e, dt, node, config )
            {
                dt.rows( { search: 'applied' } ).select();
            }
        },
        // 'selectAll',
        'selectNone',
        'selectedRows',
        @endif

        @if($table->hasDoublerButton())
        'doubler',
        @endif

    @if($table->hasSummary())
        'removeSummary',
        // @if($table->hasInlineSearch())
        // 'removeInlineSearch',
        // @endif
    @endif

    @foreach ($buttons as $button)
        @if(is_string($button))
        '{{ $button }}' @if(! $loop->last), @endif
        @elseif(is_array($button))
        {!! json_encode($button) !!} @if(! $loop->last), @endif
        @else
        {
            attr: {
                @foreach($button->getAttributes() as $attribute => $value)
                '{{ $attribute }}': '{{ $value }}',
                @endforeach
                @foreach($button->getData() as $data => $value)
                'data-{{ $data }}': '{{ $value }}',
                @endforeach
                @if($button->getId())
                id: '{{ $button->getId() }}',
                @endif
            },
            @if($buttonClasses = $button->getHtmlClassesString())
            className: '{{ $buttonClasses }}',
            @endif
            text: '{{ $button->getText() }}@if($button->hasIcon()) {!! $button->renderIcon() !!}@endif',
            @if($jsMethodText = $button->renderJsMethod())
            action: function ( e, dt, node, config ) {
                {!! $jsMethodText !!}
            }
            @endif
        } @if(! $loop->last), @endif
        @endif
    @endforeach
    ] @endif ;

</script>

@include('datatables::datatablesFields.__tableSingleSpecCss')
@include('datatables::datatablesFields.__tableSingleSpecRangeFilters')
@include('datatables::datatablesFields.__tableSingleSpecFieldOperations')
