@php

$routeParameters = [];

foreach($field->routeParameters as $paremeterName => $paremeterValue)
	$routeParameters[$paremeterName] = $table->resolveElement($paremeterValue, $element);

@endphp

<div
	@isset($field->class)
	class="{{ $field->class }}"
	@endisset 
	uk-lightbox>
	@if(isset($field->routeMethod))
		<a
			@if(isset($field->icon))
				data-type="iframe"
				uk-icon="{{ $field->icon }}"
			@endif 
			@if(isset($field->target))
				target="{{ $field->target }}"
			@endif
				href="{{ call_user_func_array([$value, $field->routeMethod], $routeParameters) . ((count($field->parameters?? []))? '&' : '?')}}iframed=true">

			@if(isset($field->label)) 
				{{ $field->label }} 
			@elseif(isset($field->labelMethod)) 
				{{ $value->{$field->labelMethod}() }} 
			@endif 
		</a> 
	@endif 
</div>

