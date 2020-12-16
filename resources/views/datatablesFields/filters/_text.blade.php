
<input
	@if($summary = $field->getSummaryType())
	data-summary="{{ $summary }}"
	@endif

	type="text"
	placeholder="{{ $field->getTranslatedName() }}"
	name="{{ $field->name }}" 
	autocomplete="notautocomplete{{ rand(0, 99999999) }}"
	data-filtertype="{{ $field->getFilterType() }}"
	/>
