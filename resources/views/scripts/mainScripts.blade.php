{{--  DATATABLES  --}}
<script type="text/javascript" src="https://cdn.datatables.net/1.10.22/js/jquery.dataTables.min.js"></script>
<script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.1.3/jszip.min.js"></script>
<script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/pdfmake.min.js"></script>
<script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/vfs_fonts.js"></script>

<script type="text/javascript" src="https://cdn.datatables.net/buttons/1.6.4/js/dataTables.buttons.min.js"></script>
<script type="text/javascript" src="https://cdn.datatables.net/buttons/1.5.6/js/buttons.colVis.min.js"></script>
<script type="text/javascript" src="https://cdn.datatables.net/buttons/1.6.4/js/buttons.html5.min.js"></script>
<script type="text/javascript" src="https://cdn.datatables.net/buttons/1.6.4/js/buttons.print.min.js"></script>
<script type="text/javascript" src="https://cdn.datatables.net/buttons/1.6.4/js/buttons.flash.min.js"></script>

<script type="text/javascript" src="https://cdn.datatables.net/select/1.3.0/js/dataTables.select.min.js"></script>
<script type="text/javascript" src="https://cdn.datatables.net/keytable/2.6.1/js/dataTables.keyTable.min.js"></script>
<script type="text/javascript" src="https://cdn.datatables.net/rowreorder/1.2.4/js/dataTables.rowReorder.min.js"></script>


{{-- <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.10.22/css/jquery.dataTables.min.css"> --}}
<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.10.19/css/dataTables.uikit.min.css"/>
<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/rowreorder/1.2.4/css/rowReorder.dataTables.min.css"/>


<link rel="stylesheet" href="https://cdn.datatables.net/select/1.3.1/css/select.dataTables.min.css" />
<link rel="stylesheet" href="https://cdn.datatables.net/keytable/2.6.1/css/keyTable.dataTables.min.css" />

<style type="text/css">
.dataTable tbody input.uk-input
{
    padding: 1px;
}    
</style>

<script type="text/javascript" src="/js/moment.min.js"></script>
{{--  //DATATABLES  --}}

@include('datatables::scripts.ajaxCallScripts')
@include('datatables::scripts.ajaxButtonScripts')
@include('datatables::scripts.buttonScripts')
@include('datatables::scripts.sortingScripts')
@include('datatables::scripts.filteringScripts')
@include('datatables::scripts.summaryScripts')
@include('datatables::scripts.utilitiesScripts')
@include('datatables::scripts.datatablesFieldsScripts')



<script type="text/javascript">

window.addParameterToURL = function(url, paramName, paramValue)
{
    url += (url.split('?')[1] ? '&':'?') + paramName + '=' + paramValue;

    return url;
}

$(document).ready(function($)
{
    $('.wannabedatatable').each(function()
    {
        let tableId = $(this).attr('id');
        let columnDefs = window[tableId + 'columnDefs'];
        let rowReorder = window[tableId + 'rowReorder'];
        let options = window[tableId + 'options'];
        let buttons = window[tableId + 'buttons'];

        let summary = (typeof $('#' + tableId).data('summary') !== 'undefined');

        let settings = {
            dom: 'Blfritip',
            processing: true,
            orderCellsTop: true,
            lengthMenu: [[10, 25, 50, 100, 250, 500, -1], [10, 25, 50, 100, 250, 500, "All"]],
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

        window['table' + tableId] = $(this).DataTable(settings)
            .on('key-blur', function ( e, datatable, cell ) {
                $(cell.node()).find('input').blur();
                $(cell.node()).find('select').blur();
            })
            .on('key-focus', function ( e, datatable, cell ) {

                setTimeout(function()
                {
                    $(cell.node()).find('input').focus();
                }, 150);
            });
    })

});   

</script>

