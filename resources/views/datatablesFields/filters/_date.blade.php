
<input
	@if($summary = $field->getSummaryType())
	data-summary="{{ $summary }}"
	@endif

	id="{{ $field->getId() }}{{ $suffix ?? '' }}"

	class="uk-input filter datefilter"

	type="date"
	placeholder="{{ $field->getTranslatedName() }}"
	name="{{ $field->name }}{{ $suffix ?? '' }}" 
	autocomplete="notautocomplete{{ rand(0, 99999999) }}"
	data-filtertype="{{ $field->getFilterType() }}"
	/>
