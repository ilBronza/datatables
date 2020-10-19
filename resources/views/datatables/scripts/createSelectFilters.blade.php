<script type="text/javascript">

$('body').on('change', '.search-selector', function()
{
    var search = [];
    var fieldId = $(this).data('fieldid');

    $.each($(this).find('option:selected'), function()
    {
        search.push($(this).val());
    });

    search = search.join('|');
    $('#' + fieldId).val(search).keyup();
});

jQuery(document).ready(function($)
{
    $('body').on('click', '.reduce-selec-filter, .reset-selec-filter', function()
    {
        var container = $(this).parents('.select_filter_row');

        var tableId = container.data('tableid');
        var columnId = container.data('column');
        var fieldId = container.data('fieldid');

        // var column = $('#' + tableId).DataTable().column(columnId);
        var column = window['table' + tableId].column(columnId);

        var target = $('#select-' + fieldId);
        var reduced = $(this).hasClass('reduce-selec-filter');

        createSelect(target, fieldId, column, reduced);
    });


    function createSelect(target, fieldId, column, reduced)
    {
        $(target).html('');

        $('#' + fieldId).val('').keyup();

        // var label = $(column.header()).data('label');

        var select = $('<select data-column="' + column.index() + '" data-table="table{{ $table->id }}" data-fieldid="' + fieldId + '" multiple class="column_filter search-selector"><option value="">&nbsp;</option></select>')
            // .appendTo( $(formControls));
            .appendTo( $(target));

        column.data().unique().sort().each( function ( d, j )
        {
            var total = column.data().filter(function(value, index)
            {
                return value == d;
            }).count();


            if(reduced)
            {
                var table = column.table();
                var filteredRows = table.rows({filter: 'applied'});

                // console.log(filteredRows);
                var counter = 0;

                filteredRows.every(function( rowIdx, tableLoop, rowLoop )
                    {
                        var value = this.data()[column.index()];
                        if (value == d)
                            counter = counter + 1;
                    });

                select.append('<option ' + ((counter == 0)? "disabled" : "") + ' value="' + d + '">' + d + ' (' + counter + '/' + total + ')</option>');                
            }
            else
                select.append('<option value="' + d + '">' + d + ' (' + total + ')</option>');                

        });

        select.select2();
    }

    function selectExists(target)
    {
        var select = $(target).children('select');

        if(select.length == 0)
            return false;

        return true;
    }

    $('.select_filter').click(function()
    {
        var tableId = $(this).closest('table').data('id');
        var columnId = $(this).data('column');

        // var column = $('#' + tableId).DataTable().column(columnId);
        var column = window['table' + tableId].column(columnId);

        var fieldId = $(this).data('fieldid');

        var target = $('#select-' + fieldId);

        if(selectExists(target))
            return false;

        createSelect(target, fieldId, column, false);
    });

    $(document).on('shown', '#modal-searches', function ()
    {
        $(this).find('.select_filter_row').each(function()
        {
            var tableId = $(this).data('tableid');

            var columnId = $(this).data('column');
            // var column = $('#' + tableId).DataTable().column(columnId);
            var column = window['table' + tableId].column(columnId);

            var fieldId = $(this).data('fieldid');

            var target = $('#select-' + fieldId);


            if(selectExists(target))
                return true;

            createSelect(target, fieldId, column, false);
        })
    });

});

</script>

