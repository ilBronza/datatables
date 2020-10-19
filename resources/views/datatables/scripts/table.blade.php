
@section('datatables.scripts.header')

@if($table->hasKeyTable())
    <link rel="stylesheet" type="text/css" href="/css/datatables/keytables.min.css"/>
    <script type="text/javascript" src="/js/datatables/keytables.min.js"></script>
@endif

@endsection



@section('scripts.header')

@if($table->mustRenderBody())
    @include('datatables::datatables.__tBodyArray')
@endif

<script>

function submitNewForm(url, ids)
{
    var form = document.createElement("form");
    form.method = "POST";
    form.action = url;

    var token = document.createElement("input"); 
    token.value=Laravel.csrfToken;
    token.name="_token";

    ids.forEach(function(item)
    {
        var data = document.createElement("input"); 
        data.value = item;
        data.name = "ids[" + item + "]";
        form.appendChild(data);
    });

    document.body.appendChild(form);

    form.appendChild(token);

    form.submit(); 
}

jQuery(document).ready(function()
{

    function getCookie(cname)
    {
        var name = cname + "=";
        var decodedCookie = decodeURIComponent(document.cookie);
        var ca = decodedCookie.split(';');

        for(var i = 0; i <ca.length; i++)
        {
            var c = ca[i];
            while (c.charAt(0) == ' ')
            {
                c = c.substring(1);
            }
            if (c.indexOf(name) == 0)
            {
                return c.substring(name.length, c.length);
            }
        }

        return "";
    }

    if (typeof window.tables === 'undefined')
        window.tables = new Array();

    window.tables.push('{{ $table->id }}');

    window.table{{ $table->id }} = $('#{{ $table->id }}').DataTable(
    {
        {{-- START decide la quantità di righe per tabella e nasconde paginazione e modifica quantità in caso di singola pagina --}}
        "lengthMenu": [[10, 25, 50, 100, 250, 500, -1], [10, 25, 50, 100, 250, 500, "All"]],
        "fnDrawCallback": function(oSettings) {
            if (oSettings._iDisplayLength == -1
                || oSettings._iDisplayLength > oSettings.fnRecordsDisplay())
            {
                jQuery(oSettings.nTableWrapper).find('.dataTables_paginate, .dataTables_length').hide();
            } else {
                jQuery(oSettings.nTableWrapper).find('.dataTables_paginate, .dataTables_length').show();
            }
        },
        {{-- END decide la quantità di righe per tabella e nasconde paginazione e modifica quantità in caso di singola pagina --}}

        "dom": '{!! $table->dom !!}',

        @if($table->saveState())
        stateSave: true,
        @endif

        @if($table->mustRenderBody())
        data: window.tabledata{{ $table->id }},
        @else
        "ajax": '{{ url()->current() }}?json',
        @endif

        @if(is_int(($rowId = $table->getRowId())))
        "rowId": {{ $rowId }},
        @endif

        "deferRender": {{ ($table->mustDeferRender())? 'true' : 'false' }},

        @if($table->datatableKeys())
        "keys": true,
        @endif

        @if($table->allowsColumnReorder())
        "colReorder": true,
        @endif

        @if($table->usesSelect())
        "select": {
            "style":    'multi',
            "selector": 'td:first-child input'
        },
        @endif

        @if(isset($table->dragAndDrop))
        rowReorder: {

            
            @if(isset($table->dragAndDrop->selector))
            selector: '{{ $table->dragAndDrop->selector }}',
            dataSrc: {{ $table->dragAndDrop->dataSrc ?? 1 }}
            @endif

        },
        @endif


        "buttons": [

            // {
            //     text: 'Archive',
            //     className: 'uk-button uk-button-primary',

            //     action: function () {

            //         var ids = window.table{{ $table->id }}.rows( { selected: true } ).ids();

            //         submitNewForm('/archive/privacybrief/', ids.toArray());
            //     }
            // },

        @if($table->mustShowColumnToggler())

            {
                extend: 'colvis',
                className: 'uk-button uk-button-primary',

                columnText: function ( dt, idx, title )
                {
                    var header = dt.column(idx).header();

                    return $(header).data('name');
                }
            },

            {
                extend: 'colvisGroup',
                text: '{{ trans('generals.all') }}',
                className: 'uk-button uk-button-primary',
                show: ':hidden',
            },

            @foreach($table->fieldsGroups as $fieldsGroup)
            {
                extend: 'colvisGroup',
                className: 'uk-button uk-button-primary',
                text: '{{ $fieldsGroup->name }}',
                show: {{ json_encode(
                    $fieldsGroup->getIndexesArray()
                    ) }},
                hide: {{ json_encode(
                    array_values(
                        array_diff(
                            $table->indexes, $fieldsGroup->getIndexesArray()
                            ))) }}
            },

            @endforeach

        @endif

        @if($table->mustShowUtilityButtons())

            {
                extend: 'copy',
                className: 'uk-button uk-button-primary uk-button-copy',
                exportOptions: {
                    columns: ':visible'
                }
            },
            {
                extend: 'csv',
                className: 'uk-button uk-button-primary uk-button-csv',
                exportOptions: {
                    columns: ':visible',
                },
                customize: function (document) {
                    let table = $(this).closest('table');

                    console.log(window.merda);
                    $(this).fadeOut();

                    return document;
                }
            }

        @endif
        ],

        @include('datatables::datatables.scripts.columnDefs')
        @include('datatables::datatables.scripts.options')

        "autoWidth": true,
        "retrieve": true, 
        "scrollX": {{ ($table->scrollX)? 'true' : 'false' }},
        // "scrollX": true,
        "pageLength": {{ $table->pageLength ?? 500}},

        @if($table->needsRawCallback())
        "rowCallback": function(row, data, index)
        {
            @include('datatables::datatables.scripts.cellClasses.columnCellClasses')
        }
        @endif
    })
    //cancella i filtri da saveState, sennò resterebbero le ricerche attive dalla precedente tabella
    @if(! $table->saveState())
    .columns().search( '' )
    @endif
    .draw();

    //utilizzo di input quando si è in datatables key navigation plugin
    window.table{{ $table->id }}
    // .on( 'key-focus', function (e, datatable, cell, originalEvent) {$('input', cell.node()).focus();})
    // .on("focus", "td input", function() {$(this).select();})
    // .on( 'key-blur', function ( e, datatable, cell ) {$('input', cell.node()).blur();})
    .on('xhr.dt', function ()
        {
            window.addSuccessNotification('Tabella {{ $table->id }} ricaricata');
        });


    window.table{{ $table->id }}filters = false;

    $('#{{ $table->id }}').parents('.dataTables_scroll').find('.dataTables_scrollHead .sectionheader input').each(function()
    {
        var column = $(this).data('column');
        if(typeof column === 'undefined')
            return;

        var cookieKey = 'table{{ $table->id }}header' + column;
        var value = getCookie(cookieKey);

        @if($table->saveState)

        $(this).val(value);
        var tableId = '{{ $table->id }}';

        if(value)
        {
            window.table{{ $table->id }}filters = true;
            filterColumn(tableId, column, this);
        }
        @endif
    });

    function filterColumn (tableId, i, element)
    {
        var table = window['table' + tableId].column(i).search(jQuery(element).val(), true, false).draw();    
        var data = table.page.info(); 
        
        if(data['recordsDisplay'] == data['recordsTotal'])
            $('#filter_alert').css('display', 'none');

        else{
            $('#filter_alert').css('display', '');
            $('#filtrati').html('Filtrati: ' + data['recordsDisplay'] + ' su: ' +  data['length']);
        }
    }

    $('#{{ $table->id }}').parents('.dataTables_scroll').find('.dataTables_scrollFoot .sectionfooter input').each(function()
    {
        var column = $(this).data('column');
        if(typeof column === 'undefined')
            return;

        var cookieKey = 'table{{ $table->id }}footer' + column;
        var bottom = getCookie(cookieKey);

        var topCookieKey = 'table{{ $table->id }}header' + column;
        var top = getCookie(topCookieKey);

        $(this).val(bottom);

        var tableId = '{{ $table->id }}';

        var tableColumn = jQuery('#{{ $table->id }}').DataTable().column(column);
        var string = '';


        if(bottom.length == 0)
        {
            if(top.length == 0)
                string = '^((?!(23595123053215912525)).)*$';

            else
                string = top;
        }
        else
        {
            if(top.length == 0)
                string = '^((?!(' + bottom + ')).)*$';

            else
                string = '(?=.*' + top + ')(?=^((?!(' + bottom + ')).)*$)';
        }

        if(bottom)
        {
            window.table{{ $table->id }}filters = true;
            tableColumn.search(string, true, false).draw();
        }
    });

    if(window.table{{ $table->id }}filters)
        window.addDangerNotification('@lang('generals.activeFiltersForTheTable')')


    $(document).on("keypress", function(e)
    { 
        if ( e.ctrlKey && ( e.which === 19 ) )
        {
            var index = window.table{{ $table->id }}.cell( { focused: true } ).index();

            var td = window.table{{ $table->id }}.cell(index.row, 0).node();
            var input = $(td).find('input');

            $(input).prop('checked', true);
            window.table{{ $table->id }}.row(index.row).select();
        }

        else if ( e.ctrlKey && ( e.which === 4 ) )
        {
            var index = window.table{{ $table->id }}.cell( { focused: true } ).index();

            var td = window.table{{ $table->id }}.cell(index.row, 0).node();
            var input = $(td).find('input');

            $(input).prop('checked', false);
            window.table{{ $table->id }}.row(index.row).deselect();
        }

    });

    window.table{{ $table->id }}.on( 'key-focus', function (e, datatable, cell, originalEvent) {

        window.focusedRow = datatable.row( cell.index().row );
    });
});

</script>


@append

@section('scripts.footer')
    
    @include('datatables::datatables.scripts._checkAll')
    @include('datatables::datatables.scripts._inputFilters')
    @include('datatables::datatables.scripts._fieldsToggler')
    @include('datatables::datatables.scripts.createSelectFilters')

@stop
