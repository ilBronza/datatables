{{--  DATATABLES  --}}
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

@include('datatables::scripts.ajaxButtonScripts')
@include('datatables::scripts.buttonScripts')
@include('datatables::scripts.sortingScripts')
@include('datatables::scripts.filteringScripts')
@include('datatables::scripts.summaryScripts')
@include('datatables::scripts.datatablesFieldsScripts')



<script type="text/javascript">
$(document).ready(function($)
{
    function addParameterToURL(url, paramName, paramValue)
    {
        url += (url.split('?')[1] ? '&':'?') + paramName + '=' + paramValue;

        return url;
    }

    $('.wannabedatatable').each(function()
    {
        let url = $(this).data('url');
        let cachedtablekey = $(this).data('cachedtablekey');

        let tableId = $(this).attr('id');
        let columnDefs = window[tableId + 'columnDefs'];
        let rowReorder = window[tableId + 'rowReorder'];
        let options = window[tableId + 'options'];
        let buttons = window[tableId + 'buttons'];

        let summary = (typeof $('#' + tableId).data('summary') !== 'undefined');

        let datatableUrl = addParameterToURL(url, 'cachedtablekey', cachedtablekey);

        let settings = {
            dom: 'Bfrtip',
            processing: true,
            orderCellsTop: true,
            // "serverSide": true,
            ajax: {
                url: datatableUrl,
                dataSrc: function(json)
                {
                    let data = window.transformDataBySummaryExistence(tableId, summary, json);

                    return data;
                }
            },
            columnDefs : columnDefs,
            rowReorder : rowReorder,
            buttons: {
                dom: {
                    button: {
                        className: 'uk-button uk-button-small uk-button-primary'
                    }
                },
                buttons: buttons
            },
            initComplete: function ()
            {
                this.api().columns().every(function ()
                {
                    // normalFilter(this, this.header());

                    // // var that = this;

                    if($(this.header()).data('range'))
                        window.rangeFilter(this, this.header());
                    else
                        window.normalFilter(this, this.header());
                });
            },
            drawCallback: function(settings)
            {
                var api = this.api();

                if(summary)
                    window.populateFilteredColumnValues(api, tableId);

                api.columns.adjust();
            }
        };

        jQuery.extend(settings, options);

        window['table' + tableId] = $(this).DataTable(settings);
    })

});   

</script>

