
<input
	@include('datatables::datatablesFields.filters._summaryDataAttributes')

	id="{{ ($isFooter ?? false)? 'footer' : '' }}{{ $field->getId() }}{{ $suffix ?? '' }}"

	class="uk-input"

	type="date"
	placeholder="{{ $field->getTranslatedName() }}"
	name="{{ ($isFooter ?? false)? 'footer' : '' }}{{ $field->name }}{{ $suffix ?? '' }}"
	autocomplete="notautocomplete{{ rand(0, 99999999) }}"
	data-filtertype="{{ $field->getFilterType() }}"

{{--	@if($width = $field->getWidth())--}}
{{--	style="max-width: {{ $width }}!important;"--}}
{{--	@endif--}}
	/>