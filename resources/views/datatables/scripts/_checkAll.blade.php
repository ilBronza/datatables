<script>

jQuery(document).ready(function()
{
    $('input.checkAll').change(function()
    {
        var that = this;
        var selected = [];
        var checked = $(that).is(':checked')? 1 : 0;

        $(this).parents('.dataTables_wrapper').find('tr td:first-of-type input[type="checkbox"]').each(function()
        {
            this.checked = checked;
            selected.push($(this).val());
        });

        var tableId = $(this).parents('table').data('id');

        if(typeof tableId !== 'undefined')
            saveChecked(tableId, checked, selected);

        else alert('Missing table ID');
    });

    function saveChecked(tableId, checked, selected)
    {
        {{--  $.ajax({
            url : "{{ route('datatable.checked.store') }}",
            type : 'POST',
            dataType : 'json',
            data : {
                tablename : tableId,
                url : window.location.href,
                checked : checked,
                selected : selected
            },
            success : function(response)
            {

            },
            error : function(response)
            {

            }
        })
        --}}
    }
});

</script>