@section('scripts.footer')

<script type="text/javascript">
    
jQuery(document).ready(function($)
{
    function getRowsIndexesByValue(table, index, content)
    {
        var ids = [];

        // Find indexes of rows which have `Yes` in the second column
        var indexes = table.rows().eq( 0 ).filter( function (rowIdx) {
            var current = $(table.cell( rowIdx, index ).node()).html();
            if((current === content))
                ids.push($(table.cell( rowIdx, index ).node()).closest('tr').attr('id'));

            return (current === content) ? true : false;
        } );

        @if(isset($multipleAssignment))
        return ids;
        @else
        return indexes;
        @endif
    }

    function replaceValue(value)
    {
        var table = window.table{{ $table->id }};

        var filtered = table.rows().eq( 0 ).filter( function (rowIdx) {

            var current = $(table.cell( rowIdx, sortingIndexColumn ).node()).html();

            if((current == value))
            {
                replaceValue(parseInt(value) + 1);

                $(table.cell( rowIdx, sortingIndexColumn ).node()).html(parseInt(value) + 1);
                return true;
            }

            return false;
        });
    }

    $('body').on('keypress', '.sortingmanualinput', function(event)
    {
        var keycode = (event.keyCode ? event.keyCode : event.which);
    
        if(keycode == '13')
        {
            window.changedSorting = true;

            let value = $(this).val();

            replaceValue(value);

            var td = $(this).closest('td');

            td.html(value);

            var cell = window.table{{ $table->id }}
            .cell(td)
            .data(value);

            window.table{{ $table->id }}.draw();
        }
    })

    $('body').on('dblclick', 'tr .sortingindex', function(e)
    {
        e.stopPropagation();

        let input = $(this).find('input');

        if(! input.length)
            $(this).append('<input class="sortingmanualinput" type="text" value="" />');

        $(this).find('input').focus();
    });


    $('body').on('dblclick', 'tr', function()
    {
        var row = window.table{{ $table->id }}.rows(this);
        var value = $(this).find('.{{ $sortingField ?? "sortingindex"}}').html();

        if(value != '')
            return false;

        var nextSeqNum = window.table{{ $table->id }}
            .column('.{{ $sortingField ?? "sortingindex"}}')
            .data()
            .sort(function(a, b)
                {
                    return a - b;
                })
            .reverse()[0];

        if(nextSeqNum == '')
            nextSeqNum = 0;

        sortingIndex = parseInt(nextSeqNum) + 1;

        window.table{{ $table->id }}.cell(this, '.{{ $sortingField ?? "sortingindex"}}').data(sortingIndex).draw();

        var rowId = $(this).attr('id');

        @if(isset($multipleAssignment))
        var commonValue = $(this).find('{{ $multipleAssignment }}').html();
        var index = $(this).closest('table').find('.sectionheader {{ $multipleAssignment }}').data('column');
        var rows = getRowsIndexesByValue(window.table{{ $table->id }}, index, commonValue)
        @endif

        if(typeof rows !== 'undefined')
            for (i = 0; i < rows.length; i++)
                $.ajax({

                    @if(isset($workstation))
                    url: '{{ route('workstations.orders.setSorting', [$workstation]) }}',
                    @elseif(isset($delivery))
                    url: '{{ route('deliveries.orders.setSorting', [$delivery]) }}',
                    @endif

                    type: 'POST',
                    data: {
                        rowId: rows[i],
                        sortingIndex: sortingIndex,
                    },
                    success: function(response)
                    {
                        window.table{{ $table->id }}.ajax.reload();
                    }
                });   
        else
            $.ajax({

                @if(isset($workstation))
                url: '{{ route('workstations.orders.setSorting', [$workstation]) }}',
                @elseif(isset($delivery))
                url: '{{ route('deliveries.orders.setSorting', [$delivery]) }}',
                @endif

                type: 'POST',
                data: {
                    rowId: rowId,
                    sortingIndex: sortingIndex,
                },
                success: function(response)
                {
                    console.log(response);
                }
            });
    });

});

</script>

@append