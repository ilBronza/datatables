<th @if($headerTooltip = $field->getHeaderTooltip()) uk-tooltip="offset: 20; title: {{ $headerTooltip }}" @elseif(config('datatables.useTooltips')) uk-tooltip="offset: 20; title: {{ $field->getTranslatedName() }}" @endif
class="{{ $field->getHeaderHtmlClasses() }} {{ Str::slug($field->getTranslatedName()) }} @if(! $field->showLabel()) hidelabel @endif"
@if($color = \IlBronza\Datatables\Support\ColumnStyles::fieldBackgroundColor($field->thBackgroundColor ?? null)) style="background-color: {{ $color }}" @endif
data-column="{{ $field->getIndex() }}" data-original-column-index="{{ $field->getIndex() }}" data-field-name="{{ $field->getFieldName() }}"
@if(method_exists($field, 'getEditorDataAttributes'))
	@foreach($field->getEditorDataAttributes() as $data => $value) data-{{ $data }}="{{ $value }}" @endforeach
@endif>
	<div @if(! $field->showLabel()) class=" uk-hidden" @endif>
		{!! $field->getTranslatedName() !!}
	</div>
	@if($icon = $field->getFetcherHeaderIconString())
		{!! $icon !!}
	@endif
	@include('datatables::datatablesFields.filters.sorting')
	@if($icon = $field->getInstationIconString())
		{!! $icon !!}
	@endif
</th>
