
<input
	@if($summary = $field->getSummaryType())
	data-summary="{{ $summary }}"
	@endif

	class="uk-input"

	type="text"
	placeholder="{{ $field->getTranslatedName() }}"
	name="{{ $field->name }}" 
	autocomplete="notautocomplete{{ rand(0, 99999999) }}"
	data-filtertype="{{ $field->getFilterType() }}"
	/>

	@include('datatables::datatablesFields.filters.filterfunctions')
