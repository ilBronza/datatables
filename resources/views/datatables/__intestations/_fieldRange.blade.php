<div class="range-container">
	
	<div class="uk-inline">

       	<a href="#modal-{{ $baseId }}" uk-toggle class="uk-form-icon uk-form-icon-flip" uk-icon="icon: cog"></a>

        <div id="modal-{{ $baseId }}" uk-modal>

            <div class="uk-modal-dialog uk-modal-body">

                <div class="range" uk-grid>
					<div class="uk-width-expand">
						
					@if($field->isDate())
						<div>
							<input 
								type='date'
								data-tableid="{{ $table->id }}"
								class="range uk-input column_filter"
								data-column="{{ $field->absoluteIndex }}"
								name='min'
								id="{{ $baseId }}min" 
								/>
						</div>
						<div>
							<input 
								type='date'
								data-tableid="{{ $table->id }}"
								class="range uk-input column_filter"
								data-column="{{ $field->absoluteIndex }}"
								name='max'
								id ="{{ $baseId }}max" 
								/>
						</div>

					@else

						<div>

							<input 
								placeholder="min"
								data-tableid="{{ $table->id }}"
								class="range uk-input column_filter"
								data-column="{{ $field->absoluteIndex }}"
								type="text"
								id="{{ $baseId }}min"
								name="min">
						</div>
						<div>
							<input
								placeholder="max"
								data-tableid="{{ $table->id }}"
								class="range uk-input column_filter"
								data-column="{{ $field->absoluteIndex }}"
								type="text"
								id="{{ $baseId }}max"
								name="max">	
						</div>
					@endif

					</div>
					<div class="uk-width-auto">
						<a class="uk-width-auto" href="javascript:void(0)" class="reset-range-filter" onclick="resetFilter(this)" uk-icon="ban"></a>
					</div>
				</div>
			</div>

		</div>

		<input placeholder="{{ trans('fields.' . $field->name) }}"
			data-tableid="{{ $table->id }}" 
			class="uk-input column_filter" 
			data-column="{{ $field->absoluteIndex }}" 
			type="text" 
			id="{{ $baseId }}precise" 
			name="precise">
		</div>
	</div>

</div>

@section('scripts.footer')

<script type="text/javascript">

$.fn.dataTable.ext.search.push(
    function( settings, data, dataIndex, rowData)
    {
    	if('{{ $table->id }}' != settings.nTable.getAttribute('id'))
    		return true;

		@if($field->isDate())

		var min = $('#{{ $baseId }}min').data('timestamp');
		var max = $('#{{ $baseId }}max').data('timestamp');

		var value = rowData[{{ $field->absoluteIndex }}];

		@else

        var min = parseInt( $('#{{ $baseId }}min').val(), 10 );
        var max = parseInt( $('#{{ $baseId }}max').val(), 10 );
        var value = parseFloat( data[{{ $field->absoluteIndex }}] ) || 0;

        @endif

        if ( ( isNaN( min ) && isNaN( max ) ) ||
             ( isNaN( min ) && value <= max ) ||
             ( min <= value   && isNaN( max ) ) ||
             ( min <= value   && value <= max ) )
        {
            return true;
        }
        return false;
    }
);

</script>
@append
