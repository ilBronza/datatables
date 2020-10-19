@if($value)
	@lang('fields.boolean_1')
@else
	<a
		class="uk-button dg-icon dg-icon-{{ $element->getOrderDossiersLink()->icon }}"
		href="{{ $element->getOrderDossiersLink()->href }}">&nbsp;</a>
@endif
