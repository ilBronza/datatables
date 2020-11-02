{{--  DATATABLES  --}}
<script type="text/javascript" src="https://cdn.datatables.net/1.10.22/js/jquery.dataTables.min.js"></script>

<script type="text/javascript" src="https://cdn.datatables.net/1.10.22/js/jquery.dataTables.min.js"></script>
<script type="text/javascript" src="https://cdn.datatables.net/buttons/1.6.4/js/dataTables.buttons.min.js"></script>
<script type="text/javascript" src="https://cdn.datatables.net/buttons/1.6.4/js/buttons.flash.min.js"></script>
<script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.1.3/jszip.min.js"></script>
<script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/pdfmake.min.js"></script>
<script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/vfs_fonts.js"></script>
<script type="text/javascript" src="https://cdn.datatables.net/buttons/1.6.4/js/buttons.html5.min.js"></script>
<script type="text/javascript" src="https://cdn.datatables.net/buttons/1.6.4/js/buttons.print.min.js"></script>


<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.10.22/css/jquery.dataTables.min.css">
<script type="text/javascript" src="/js/moment.min.js"></script>
{{--  //DATATABLES  --}}


<script type="text/javascript">
$(document).ready(function($) {

    $.fn.dataTable.ext.buttons.reload = {
        text: 'Reload',
        action: function ( e, dt, node, config ) {
            dt.ajax.reload();
        }
    };

    function addParameterToURL(url, paramName, paramValue)
    {
        url += (url.split('?')[1] ? '&':'?') + paramName + '=' + paramValue;

        return url;
    }

    $('th input').click(function(e)
    {
        e.stopPropagation();
    });

    function transformDataBySummaryExistence(tableId, summary, json)
    {
        if(summary)
        {
            let data = json.data;

            let summaryValues = json.data.pop();
            let summaryRow = $('#' + tableId).find('thead tr.summary');

            summaryValues.forEach(function(element, index)
            {
                let th = $(summaryRow).find('.summary' + index);
                $(th).html(element);
            });
        }

        return json.data;
    }

    function populateFilteredColumnValues(api, tableId)
    {
        let filteredRows = api.rows({filter:'applied'}).data();

        $('#' + tableId).find('thead tr.columns th').each(function(columnIndex, element)
        {
            let summary = $(element).find('input').data('summary');

            if(typeof summary === 'undefined')
                return;

            if((summary == 'average')||(summary == 'sum'))
            {
                let result = 0;
                let totalRowsFilteredCount = 0;

                api.column(columnIndex, { search:'applied' } ).data().each(function(value, rowIndex) {
                    if(! isNaN(float = parseFloat(value)))
                        result = result + float;

                    totalRowsFilteredCount = rowIndex;
                });

                if(summary == 'average')
                    result = Math.round(((result / (totalRowsFilteredCount + 1)) + Number.EPSILON) * 100) / 100

                let th = $('#' + tableId + ' .inlinesearchsummary').find('.summary' + columnIndex);
                $(th).html(result);
            }
        });

    }

    $('.wannabedatatable').each(function()
    {
        let url = $(this).data('url');
        let cachedtablekey = $(this).data('cachedtablekey');

        let tableId = $(this).attr('id');
        let columnDefs = window[tableId + 'columnDefs'];
        let buttons = window[tableId + 'buttons'];

        let summary = (typeof $('#' + tableId).data('summary') !== 'undefined');

        let datatableUrl = addParameterToURL(url, 'cachedtablekey', cachedtablekey);

        $(this).DataTable( {
            dom: 'Bfrtip',
            processing: true,
            orderCellsTop: true,
            // "serverSide": true,
            ajax: {
                url: datatableUrl,
                dataSrc: function(json)
                {
                    let data = transformDataBySummaryExistence(tableId, summary, json);
                    
                    return data;
                }
            },
            columnDefs : columnDefs,
            buttons: buttons,
            // columns: [
            // // {
            // //     // DT_RowClass : 0
            // // }
            // ],
            initComplete: function ()
            {
                this.api().columns().every(function ()
                {
                    var that = this;
     
                    $('input', this.header()).on('keyup change clear', function ()
                    {
                        if(that.search() !== this.value)
                        {
                            that
                            .search(this.value)
                            .draw();
                        }
                    });
                });
            },
            drawCallback: function(settings)
            {
                var api = this.api();

                if(summary)
                    populateFilteredColumnValues(api, tableId);
            }
        });        
    })
});     
</script>

