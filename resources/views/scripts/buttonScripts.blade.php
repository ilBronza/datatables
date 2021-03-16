<script type="text/javascript">

$(document).ready(function($)
{
    $.fn.dataTable.ext.buttons.reload = {
        text: 'Reload',
        action: function ( e, dt, node, config ) {
            dt.ajax.reload();
        }
    };

    $.fn.dataTable.ext.buttons.removeSummary = {
        text: 'Riepilogo',
        action: function ( e, dt, node, config ) {

            $(node).toggleClass('uk-button-danger');

            // let mytitle = dt.column( idx ).header().innerHTML;
            let tableId = $(dt.column( 0 ).header()).parents('table').attr('id');
            let summaryRow = $('#' + tableId).find('tr.summary');

            $(summaryRow).toggle();
        }
    };

    $.fn.dataTable.ext.buttons.removeInlineSearch = {
        text: 'Riepilogo filtrato',
        action: function ( e, dt, node, config ) {

            $(node).toggleClass('uk-button-danger');

            // let mytitle = dt.column( idx ).header().innerHTML;
            let tableId = $(dt.column( 0 ).header()).parents('table').attr('id');
            let summaryRow = $('#' + tableId).find('tr.inlinesearchsummary');

            $(summaryRow).toggle();
        }
    };

    
});
    
</script>