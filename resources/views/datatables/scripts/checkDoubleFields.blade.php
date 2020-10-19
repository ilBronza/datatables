@section('scripts.footer')

<script type="text/javascript">
    
jQuery(document).ready(function($)
{
    function findDuplicates(field, table)
    {

    }

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

    window.table{{ $table->id }}.on( 'draw', function () {
        findDuplicates('{{ $fieldChecker }}', window.table{{ $table->id }});
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