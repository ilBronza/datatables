
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

		var min = $('#{{ $field->getId() }}start').data('timestamp');
		var max = $('#{{ $field->getId() }}end').data('timestamp') + (24 * 60 * 60);

		// console.log($('#{{ $field->getId() }}end').data('timestamp'));

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

	
</script>