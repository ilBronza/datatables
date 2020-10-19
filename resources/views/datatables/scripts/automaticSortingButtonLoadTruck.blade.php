@section('scripts.footer')

<script type="text/javascript">
    
jQuery(document).ready(function($)
{
    window.changedSorting = false;
    window.allTableNames = [];
    window.emptyTableNames = [];
    window.populatedTableNames = [];
    window.maxIndexGiven = 0;

    window.datatable = $('#{{ $table->id }}').DataTable();
    window.sortingIndexColumn = $('#{{ $table->id }}').find('.sectionheader .{{ $columnSortingIndex }}').data('column');
    window.nameIndedxColumn = $('#{{ $table->id }}').find('.sectionheader .{{ $columnClass }}').data('column');

    function myConfirmation() {
        if(window.changedSorting)
            return 'Are you sure you want to quit?';
    }

    window.onbeforeunload = myConfirmation;

    function getTableArrayNames()
    {
        datatable.rows().eq( 0 ).map( function (rowIdx)
        {
            var name = String(datatable.cell( rowIdx, nameIndedxColumn ).data());
            var sortingIndex = parseInt(datatable.cell( rowIdx, sortingIndexColumn ).data());

            if(typeof allTableNames[name] === 'undefined')
                allTableNames[name] = [];

            if(isNaN(sortingIndex))
            {
                allTableNames[name][rowIdx] = 'vuoto';

                if(typeof emptyTableNames[name] === 'undefined')
                    emptyTableNames[name] = [];

                emptyTableNames[name].push(rowIdx);
            }
            else
            {
                allTableNames[name][rowIdx] = sortingIndex;

                if(maxIndexGiven < sortingIndex)
                    maxIndexGiven = sortingIndex;

                if(typeof populatedTableNames[name] === 'undefined')
                    populatedTableNames[name] = [];

                populatedTableNames[name][rowIdx] = sortingIndex;
            }
        });
    }

    function findLowerIndex(names)
    {
        var index = 99999999;

        names.forEach(function(sortinIndex, rowIdx)
        {
            if(sortinIndex < index)
                index = sortinIndex;
        });

        return index;
    }

    // function assignSortingIndex()
    // {
    //     getTableArrayNames();

    //     var currentIndex = maxIndexGiven;


    //     datatable.rows().eq( 0 ).map( function (rowIdx)
    //     {
    //         var sortingIndex = parseInt(datatable.cell( rowIdx, sortingIndexColumn ).data());            
    //         var name = String(datatable.cell( rowIdx, nameIndedxColumn ).data());

    //         console.log('\n\nsortingIndex');
    //         console.log(sortingIndex);
    //         console.log(name);
    //         console.log(populatedTableNames);

    //         //se ha già un valore esco
    //         if(! isNaN(sortingIndex))
    //             return;

    //         //se esiste un valore precedente lo assegno FUNZIONA
    //         if(typeof populatedTableNames[name] !== 'undefined')
    //         {
    //             var newSortingIndex = findLowerIndex(populatedTableNames[name]);
    //             datatable.cell( rowIdx, sortingIndexColumn ).data(newSortingIndex);
    //         }
    //         else
    //         {
    //             datatable.cell( rowIdx, sortingIndexColumn ).data(++currentIndex);

    //             allTableNames[name] = allTableNames[name].filter(Boolean);

    //             //if is single we can give simply new currentIndex
    //             if(allTableNames[name].length == 1)
    //                 return;

    //             populatedTableNames[name] = [];
    //             populatedTableNames[name][rowIdx] = currentIndex;
    //         }
    //     });

    //     datatable.draw();
    // }

    function storeSortingIndexes()
    {
        var sortingData = new Object();

        datatable.rows().eq( 0 ).map( function (rowIdx)
        {
            var rowId = datatable.cell( rowIdx, 0 ).data();
            var sortingIndex = parseInt(datatable.cell( rowIdx, sortingIndexColumn ).data());

            if(isNaN(sortingIndex))
                sortingIndex = null;

            sortingData[rowId] = sortingIndex;
        });

        $.ajax({
            url: '{{ route('deliveries.orders.setMassSorting', [$delivery]) }}',

            type: 'POST',
            data: {
                indexes : sortingData
            },
            success: function(response)
            {
                window.changedSorting = false;
                window.addSuccessNotification('ordinamento salvato');
            }
        })
    }

    function showDuplicates()
    {
        datatable.rows().eq( 0 ).map( function (rowIdx)
        {
            var nameCell = datatable.cell( rowIdx, nameIndedxColumn );
            var name = String(nameCell.data());

            if(allTableNames[name].filter(Boolean).length > 1)
            {
                console.log($(nameCell.node()).addClass('uk-text-danger'));
            }
        });
    }

    // $('.automatic-order').click(function()
    // {
    //     assignSortingIndex();
    //     // storeSortingIndexes();
    // });

    $('.store-ordering').click(function()
    {
    //     // assignSortingIndex();
        storeSortingIndexes();
    });

    datatable.on( 'init', function () {
        getTableArrayNames();
        showDuplicates();
    } );

    $('.reduce-order-index').click(function()
    {
        var newSortingIndex = 0;
        var previousIndex = 0;

        datatable.rows().eq( 0 ).map( function (rowIdx)
        {
            var sortingIndex = parseInt(datatable.cell( rowIdx, sortingIndexColumn ).data());

            if(previousIndex < sortingIndex)
                ++newSortingIndex;

            if(isNaN(sortingIndex))
                return false;

            gap = sortingIndex - newSortingIndex;

            datatable.cell( rowIdx, sortingIndexColumn ).data(sortingIndex - gap);
            previousIndex = sortingIndex;
        });

    });
});

</script>

@append


<a href="{{ route('deliveries.orders.resetSorting', ['delivery' => $delivery->getKey()]) }}" class="reduce-order-index uk-button uk-button-default uk-button-small">Resetta valori</a>
<a href="javascript:void(0)" class="reduce-order-index uk-button uk-button-default uk-button-small">Riduci valori</a>
{{-- <a href="javascript:void(0)" class="automatic-order uk-button uk-button-default uk-button-small">Ordina automaticamente</a> --}}
<a href="javascript:void(0)" class="store-ordering uk-button uk-button-default uk-button-small">Salva ordinamento</a>
