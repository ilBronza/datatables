<span class="uk-h4">{{ $field->getTranslatedName() }}</span>

<div>

	@if($field->getFilterType() == 'text')
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
	@elseif($field->getFilterType() == 'date')
		<input
			@if($summary = $field->getSummaryType())
			data-summary="{{ $summary }}"
			@endif

			type="date"
			placeholder="{{ $field->getTranslatedName() }}"
			name="{{ $field->name }}" 
			autocomplete="notautocomplete{{ rand(0, 99999999) }}"
			data-filtertype="{{ $field->getFilterType() }}"
			/>		
	@endif
</div>
