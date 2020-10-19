@if($item = $value->{$field->element}->first())
 	@include('datatables::datatables.fields.json._' . $field->subView['view'], [
		'value' => (isset($field->parameter))? $item->{$field->parameter} : $item,
		'field' => (object) $field->subView,
		])
@endif