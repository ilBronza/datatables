
<input
	@include('datatables::datatablesFields.filters._summaryDataAttributes')

	id="{{ ($isFooter ?? false)? 'footer' : '' }}{{ $field->getId() }}start"

	class="uk-input"
	type="text"
	placeholder="{{ $field->getTranslatedName() }}"
	name="{{ ($isFooter ?? false)? 'footer' : '' }}{{ $field->name }}start"
	autocomplete="notautocomplete{{ rand(0, 99999999) }}"
	data-filtertype="{{ $field->getFilterType() }}"
	/>

<br />

<input
	@include('datatables::datatablesFields.filters._summaryDataAttributes')

	id="{{ ($isFooter ?? false)? 'footer' : '' }}{{ $field->getId() }}end"

	class="uk-input"
	type="text"
	placeholder="{{ $field->getTranslatedName() }}"
	name="{{ ($isFooter ?? false)? 'footer' : '' }}{{ $field->name }}end"
	autocomplete="notautocomplete{{ rand(0, 99999999) }}"
	data-filtertype="{{ $field->getFilterType() }}"
	/>