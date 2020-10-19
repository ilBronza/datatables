<input

	@if($field->isSelect())
	href="#modal-searches"
	uk-toggle
	@endif

	id="{{ $baseId }}"

	class="uk-input column_filter {{ $field->isSelect()? 'select_filter' : '' }}"
	type="text"
	data-fieldid="{{ $baseId }}"
	data-column="{{ $field->absoluteIndex }}"
	placeholder="{{ trans('fields.' . $field->name) }}" />
