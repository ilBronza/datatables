@if($item = $value->first())
 	@include('datatables::datatables.fields.json._' . $field->subView['view'], [
		'value' => (isset($field->parameter))? $item->{$field->parameter} : $item,
		'field' => (object) $field->subView,
		])
@endif