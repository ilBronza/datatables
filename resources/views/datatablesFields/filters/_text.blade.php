
<input
	@if($summary = $field->getSummaryType())
	data-summary="{{ $summary }}"
	@endif

	class="uk-input"

	type="text"

	@if($field->mustPrintIntestation())
		placeholder="{{ trans('datatables::fields.search') }}"
	@else
		placeholder="{{ $field->getTranslatedName() }}"
	@endif

	name="{{ ($isFooter ?? false)? 'footer' : '' }}{{ $field->name }}"
	autocomplete="notautocomplete{{ rand(0, 99999999) }}"
	data-filtertype="{{ $field->getFilterType() }}"
	/>