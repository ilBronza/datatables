
<input
	@if($summary = $field->getSummaryType())
	data-summary="{{ $summary }}"
	@endif

	id="{{ $field->getId() }}start"

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

	type="text"
	placeholder="{{ $field->getTranslatedName() }}"
	name="{{ $field->name }}end" 
	autocomplete="notautocomplete{{ rand(0, 99999999) }}"
	data-filtertype="{{ $field->getFilterType() }}"
	/>
