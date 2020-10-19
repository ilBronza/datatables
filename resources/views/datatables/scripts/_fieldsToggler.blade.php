<script type="text/javascript">

$('input.fields-group-toggler').click(function()
{
    var container = $(this).closest('.fields-toggler-group').find('.fields-container-parent');

    var checked = $(this).is(':checked');

    $(container).find('input').each(function()
    {
        if($(this).is(':checked') != checked)
            $(this).click();
    });
});

$('.fields-container input').on( 'change', function (e)
{
    e.preventDefault();

    var tableId = $(this).closest('.fields-toggler-container').data('tableid');
    var tableName = 'table' + tableId;

    var field = $(this).data('field');

    var column = window[tableName].column( $(this).data('column'));
    var visible = $(this).is(':checked');

    column.visible( visible );

    {{--  $.ajax({
        url : "{{ route('datatable.fields.showing') }}",
        method : 'POST',
        data : {
            tablename : tableId,
            field : field,
            visible : visible
        },
        success(response)
        {
            console.log(response);
        }
    });
    --}}
});

</script>