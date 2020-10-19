@php
	$colCounter = $table->mustShowRowToggler()? 1 : 0;

	if(in_array($element->id, $table->selectedElements)) : 
		$_classes = " checked checkedtrue";
		$checked = 'checked="checked"';
	endif;

@endphp

<tr 
data-index="{{ $loop->index }}" 
data-id="{{ $element->id }}" 
class="uk-text-small status{{ $element->status?? '' }} {{ $element->status?? '' }} {{ $_classes?? '' }}">

@if($table->mustShowRowToggler())
<td data-column='0'>
	<input 
	{{-- data-tableid="{{ $table->id }}" --}}
	type="checkbox" 
	name="checked[{{ $element->id }}]"

	{{ $checked?? '' }}

	class="checkIt"
	value="{{ $element->id }}" />
</td>
@endif

@foreach($table->fieldsGroups as $group)
	@foreach($group->fields as $field)

	<td class="{{ str_replace('.', ' ', $field->name) }}" data-column='{{ $field->absoluteIndex }}'>
		@if($field->view)

			@include('datatables::datatables.fields._' . $field->view, [
				'value' => resolveElement($field->name, $element)
				])

{{-- 			@include('tables.fields._' . $field->view, [
				//'value' => resolveElement($field->name, $element),
				//'types' => resolveLink($field->view)
				])
 --}}
		@else
		{{ json_encode($column) }}		
		@endif
	</td>

	@endforeach
@endforeach

</tr>