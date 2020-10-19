<input
	type="text"
	class="uk-input moving tripposition position"
	id="position-{{ $element->id }}"
	name="position[{{ $element->id }}]"
	placeholder="fields.position"
	autoComplete="off"
	data-originalvalue="{{ $element->position }}"
	value="{{ $element->position}}" />