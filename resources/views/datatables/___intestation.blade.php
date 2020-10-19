<tr class="section{{ $position }}">

	@foreach($table->fieldsGroups as $group)
		@foreach($group->fields as $field)
			@if($field->name == 'rowToggler')
				@include('datatables::datatables.__intestations.toggler')
			@else
				<th
					data-name="{{ $field->name }}"
					data-column="{{ $field->absoluteIndex }}"
					uk-tooltip="@lang('fields.' . $field->name)"
					>
					<span style="display: none;">@lang('fields.' . $field->name)</span>
					<div
						{{--  style="max-height: 40px;outline: 1px solid red;overflow: visible;" --}}
						>
						@include('datatables::datatables.__intestations.field')
					</div>
				</th>

			@endif
		@endforeach
	@endforeach

</tr>