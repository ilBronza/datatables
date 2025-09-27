<th @if(config('datatables.useTooltips')) uk-tooltip="offset: 20; title: {{ $field->getTranslatedName() }}" @endif
class="{{ $field->getHeaderHtmlClasses() }} {{ Str::slug($field->getTranslatedName()) }} @if(! $field->showLabel()) hidelabel @endif">
	<div @if(! $field->showLabel()) class=" uk-hidden" @endif>
		{!! $field->getTranslatedName() !!}
	</div>
</th>
