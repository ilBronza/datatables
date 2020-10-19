@section('scripts.footer')

<script type="text/javascript">
    
jQuery(document).ready(function($)
{
    window.table{{ $table->id }}.on( 'row-reorder', function ( e, diff, edit )
    {
        window.changedSorting = true;

        var sortingData = new Object();

        for ( var i=0, ien=diff.length ; i<ien ; i++ )
        {
            var rowData = window.table{{ $table->id }}.row( diff[i].node ).data();
            var rowId = rowData[{{ $table->rowId }}];
            var newPosition = diff[i].newData;

            sortingData[rowId] = newPosition;
        }

        $.ajax({

            @if(isset($workstation))
            url: '{{ route('workstations.orders.setMassSorting', [$workstation]) }}',
            @elseif(isset($delivery))
            url: '{{ route('deliveries.orders.setMassSorting', [$delivery]) }}',
            @endif

            type: 'POST',
            data: {
                indexes : sortingData
            },
            success: function(response)
            {
                console.log(response);
            }
        })
    });
});

</script>

@append