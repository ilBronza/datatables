
<div class="datatablefilter">
	@include('datatables::datatablesFields.filters._date', ['suffix' => 'start'])
</div>
<div class="datatablefilter">
	@include('datatables::datatablesFields.filters._date', ['suffix' => 'end'])
</div>

<script type="text/javascript">

$.fn.dataTable.ext.search.push(
	function( settings, data, dataIndex, rowData)
	{
		if('{{ $field->table->getId() }}' != settings.nTable.getAttribute('id'))
			return true;

        var min = window['range{{ $field->getId() }}start'];
        var max = window['range{{ $field->getId() }}end'];

		var date = rowData[{{ $field->getIndex() }}];

		if(Array.isArray(date))
		{
			if (date[date.length - 1] !== 'undefined')
			{
				if (!isNaN(float = parseFloat(date[date.length - 1])))
					value = float;
			}
		}
		else if (!isNaN(float = parseFloat(date)))
			value = float;
		else value = date;

        if ( ( isNaN( min ) && isNaN( max ) ) ||
			 ( isNaN( min ) && value <= max ) ||
			 ( min <= value   && isNaN( max ) ) ||
			 ( min <= value   && value <= max ) )
		{
			return true;
		}

		return false;
	}
);

$('#{{ $field->getId() }}start, #{{ $field->getId() }}end').change(function()
{
    let dateValue = (new Date($(this).val()));

    window['range' + $(this).attr('id')] = dateValue.valueOf() / 1000 - (3600 * 2);

    if($(this).attr('id') == '{{ $field->getId() }}end')
        window['range' + $(this).attr('id')] = window['range' + $(this).attr('id')] + (60 * 60 * 24) - 1;

    $('#{{ $field->table->getId() }}').DataTable().draw();
});

</script>