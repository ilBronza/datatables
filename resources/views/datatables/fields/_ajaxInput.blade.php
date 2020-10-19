<div
	class="ajax-form input{{ $field->inputName }} uk-grid-collapse"
	data-type="POST"
	data-url="{{ $value->{$field->routeMethod}() }}"
	uk-grid>

	<div class="uk-width-expand">
		
	@include('forms.__' . $field->inputType, [
		'input' => array(
			'noLabel' => true,
			'name' => $field->inputName,
			'value' => $value->getValueFor($field->inputName)
		)])

	</div>
	<div class="uk-width-auto">
		<a class="uk-alert-success ajax-submit" href="javasctipt:void(0)" uk-icon="push"></a>
	</div>
</div>