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

            $(node).parents('.dataTables_wrapper').find('tr.summary').toggle();
        }
    };

    $.fn.dataTable.ext.buttons.removeInlineSearch = {
        text: 'Riepilogo filtrato',
        action: function ( e, dt, node, config )
        {
            $(node).toggleClass('uk-button-danger');

            $(node).parents('.dataTables_wrapper').find('tr.inlinesearchsummary').toggle();
        }
    };

    
});
    
</script>