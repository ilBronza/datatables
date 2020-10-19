<div
	@isset($field->class)
	class="{{ $field->class }}"
	@endisset

	uk-lightbox>

	<a 
		@if(isset($field->icon))
		data-type="iframe" uk-icon="{{ $field->icon }}" 
		@endif

		@if(isset($field->target))
		target="{{ $field->target }}"
		@endif

	@if(isset($field->routeMethod))
		href="{{ call_user_func_array([$value, $field->routeMethod], compact($field->parameters ?? [])) . ((count($field->parameters?? []))? '&' : '?')}}iframed=true">
	@else
		href="{{ route($field->route) . ((count($field->parameters?? []))? '&' : '?')}}iframed=true">
	@endif 

		@if(isset($field->label))
			{{ $field->label }}
		@elseif(isset($field->labelMethod))

		{{ $value->{$field->labelMethod}() }}
	</a>

@endif
</div>