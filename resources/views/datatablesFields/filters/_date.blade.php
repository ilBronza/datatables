
<input
	@if($summary = $field->getSummaryType())
	data-summary="{{ $summary }}"
	@endif

	id="{{ $field->getId() }}{{ $suffix ?? '' }}"

	class="uk-input"

	type="date"
	placeholder="{{ $field->getTranslatedName() }}"
	name="{{ $field->name }}{{ $suffix ?? '' }}" 
	autocomplete="notautocomplete{{ rand(0, 99999999) }}"
	data-filtertype="{{ $field->getFilterType() }}"

{{--	@if($width = $field->getWidth())--}}
{{--	style="max-width: {{ $width }}!important;"--}}
{{--	@endif--}}
	/>

	@include('datatables::datatablesFields.filters.filterfunctions')
