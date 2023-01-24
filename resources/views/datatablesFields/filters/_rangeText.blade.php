
<input
	@if($summary = $field->getSummaryType())
	data-summary="{{ $summary }}"
	@endif

	id="{{ $field->getId() }}start"

	class="uk-input"
	type="text"
	placeholder="{{ $field->getTranslatedName() }}"
	name="{{ $field->name }}start" 
	autocomplete="notautocomplete{{ rand(0, 99999999) }}"
	data-filtertype="{{ $field->getFilterType() }}"
	/>

<br />

<input
	@if($summary = $field->getSummaryType())
	data-summary="{{ $summary }}"
	@endif

	id="{{ $field->getId() }}end"

	class="uk-input"
	type="text"
	placeholder="{{ $field->getTranslatedName() }}"
	name="{{ $field->name }}end" 
	autocomplete="notautocomplete{{ rand(0, 99999999) }}"
	data-filtertype="{{ $field->getFilterType() }}"
	/>

<script type="text/javascript">
$.fn.dataTable.ext.search.push(
	function( settings, data, dataIndex, rowData)
	{
		if('{{ $field->table->getId() }}' != settings.nTable.getAttribute('id'))
			return true;

		var min = parseInt( $('#{{ $field->getId() }}start').val(), 10 );
		var max = parseInt( $('#{{ $field->getId() }}end').val(), 10 );

		var number = data[{{ $field->getIndex() }}];

		if(Array.isArray(number))
		{
			if (number[number.length - 1] !== 'undefined')
			{
				if (!isNaN(float = parseFloat(number[number.length - 1])))
					value = float;
			}
		}
		else if (!isNaN(float = parseFloat(number)))
			value = float;
		else value = number;

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