<div>
	<label for="data-table{{ $table->id }}{{ $field->absoluteIndex }}">
		<span class="uk-text-truncate">{{ trans('fields.' . $field->name) }}</span>

		<input
			class="uk-checkbox"
			type="checkbox"
			data-field="{{ $field->name }}"

			@if(! ($field->hidden ?? false))
			checked=""
			@endif

			id="data-table{{ $table->id }}{{ $field->absoluteIndex }}"
			
			data-column="{{ $field->absoluteIndex }}"
			/> 
	</label>				
</div>
