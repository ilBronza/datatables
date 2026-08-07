<th @if(config('datatables.useTooltips')) uk-tooltip="offset: 20; title: {{ $field->getTranslatedName() }}" @endif
class="{{ $field->getHeaderHtmlClasses() }} {{ Str::slug($field->getTranslatedName()) }} @if(! $field->showLabel()) hidelabel @endif"
data-column="{{ $field->getIndex() }}">
	<div @if(! $field->showLabel()) class=" uk-hidden" @endif>
		{!! $field->getTranslatedName() !!}
	</div>
	@include('datatables::datatablesFields.filters.sorting')
	@if($icon = $field->getInstationIconString())
		{!! $icon !!}
	@endif
</th>
