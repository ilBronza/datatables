@if(isset($field->routeMethod))
<a 
	@if(isset($field->icon))
	uk-icon="{{ $field->icon }}"
	@endif
	href="{{ call_user_func_array([$value, $field->routeMethod], compact($field->parameters ?? [])) }}">

	@if(isset($field->label))
		{{ $field->label }}
	@elseif(isset($field->labelMethod))
		{{ $value->{$field->labelMethod}() }}
	@endif

</a>
@endif