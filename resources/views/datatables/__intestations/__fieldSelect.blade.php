@section('modalsSearch' . ucfirst($position))

<div class="uk-form-stacked select_filter_row"
	data-tableid="{{ $table->id }}" 
	data-fieldid="{{ $baseId }}" 
	data-column="{{ $field->absoluteIndex }}"
>
	<div class="uk-margin">
		<div class="uk-form-label">
			<div uk-grid>							
				<div class="uk-width-expand">
					{{ trans('fields.' . $field->name) }}
				</div>
				<div class="uk-width-auto">

					<a
						href="javascript:void(0)"
						class="uk-button reset-selec-filter"
						>
						<span class="dg-icon dg-icon-reset">R</span>
					</a>

					<a
						href="javascript:void(0)"
						class="uk-button reduce-selec-filter"
						>
						<span class="dg-icon dg-icon-reduce">F</span>
					</a>
						
				</div>
			</div>
		</div>
		<div class="uk-form-controls" id="select-{{ $baseId }}">
		</div>
	</div>
</div>

@append
