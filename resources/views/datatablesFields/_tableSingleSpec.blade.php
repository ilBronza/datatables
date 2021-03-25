<script type="text/javascript">

    window.{{ $table->getId() }}FilteredSelected = false;

    window.{{ $table->getId() }}rowReorder = @isset($table->dragAndDrop) {
   
            @if(isset($table->dragAndDrop->selector))
            selector: '{{ $table->dragAndDrop->selector }}',
            dataSrc: {{ $table->dragAndDrop->dataSrc ?? 1 }}
            @endif

        } @else false @endisset ;

    window.{{ $table->getId() }}options =  { 

        @if($table->hasSelectRowCheckboxes())
        select: {
            style:    'multi',
            selector: 'td:first-child'
        },
        @endif

        @if($scripts = $table->getCreatedRowScripts())
        createdRow: function( row, data, dataIndex )
        {
            @foreach ($scripts as $script)
            {!! $script !!}
            @endforeach
        },        
        @endif

        pageLength: {{ $table->getPageLength() }},

        @if(($rowIdIndex = $table->getRowIdIndex()) !== null)
        rowId: {{ $rowIdIndex }},
        @endif

            @foreach($table->options as $name => $value)
                @if($name == 'order')
        "{{ $name }}" : 
        [
            //{!! json_encode($value) !!}
            @foreach($value as $index => $order)

            {!! json_encode($order) !!}@if(! $loop->last),
                @endif
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

    @foreach ($table->customColumnDefs as $customColumnDef)
        {!! $customColumnDef !!},
    @endforeach
    @endif ];

    window.{{ $table->getId() }}buttons = @if(count($buttons = $table->getButtons())||(count(1))) [

        @if($table->hasSelectRowCheckboxes())
        {
            extend: 'selected',
            text: 'Solo selezionate',
            action: function ( e, dt, button, config )
            {
                if (button.text() == 'Solo selezionate') {
                    window.{{ $table->getId() }}FilteredSelected = true;
                    dt.rows({ selected: false }).nodes().to$().css({"display":"none"});
                    button.text('Tutte le righe');
                }
                else {
                    window.{{ $table->getId() }}FilteredSelected = false;
                    dt.rows({ selected: false }).nodes().to$().css({"display":"table-row"});
                    button.text('Solo selezionate');
                }

                window['table{{ $table->getId() }}'].draw();
            }
        },
        @endif


    @if($table->hasSummary())
        'removeSummary',
        @if($table->hasInlineSearch())
        'removeInlineSearch',
        @endif
    @endif

    @foreach ($buttons as $button)
        @if(is_string($button))
        '{{ $button }}' @if(! $loop->last), @endif
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
            @if($buttonClasses = $button->getClasses())
            className: '{{ $buttonClasses }}',
            @endif
            text: '{{ $button->getName() }}',
            @if($jsMethodText = $button->renderJsMethod())
            action: function ( e, dt, node, config ) {
                {!! $jsMethodText !!}
                // dt.ajax.reload();
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
