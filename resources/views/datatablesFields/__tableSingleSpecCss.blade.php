<style type="text/css">

	{!! $table->getHeaderStylesCss() !!}

	@foreach($table->getFields() as $index => $field)

		/*colonna indice {{ $loop->index }}*/

		@if($field->hasCss())
	#{{ $table->getId() }} td.{{ $field->getHtmlClassForCss() }} {
		@foreach($field->getCssRules() as $cssRule)
    	{{ $cssRule }};
	    @endforeach
    }
	   @endif

	@if($alignmentString = $field->getAlignmentCssString())
		{{-- #{{ $table->getId() }} th.{{ $field->getHtmlClassForCss() }} @if($field->isEditor()) input @endif, --}}
       	#{{ $table->getId() }} td.{{ $field->getHtmlClassForCss() }} @if($field->isEditor()) input @endif
	{
		{{ $alignmentString }}
	}
	@endif

/*lemme lemme*/
/*{!! json_encode($field->name) !!}*/

    @if((! empty($width = (method_exists($field, 'getUnstyledWidth') ? $field->getUnstyledWidth() : $field->getWidth())))&&($width != 'auto'))

		#{{ $table->getId() }} th.{{ $field->getHtmlClassForCss() }},
		#{{ $table->getId() }} td.{{ $field->getHtmlClassForCss() }},
		#{{ $table->getId() }}_wrapper th.{{ $field->getHtmlClassForCss() }},
		#{{ $table->getId() }}_wrapper td.{{ $field->getHtmlClassForCss() }},
		/* FixedHeader v3 clones the table and moves it outside the wrapper. */
		table.fixedHeader-floating.{{ $table->getName() }} th.{{ $field->getHtmlClassForCss() }},
		/* Compatibility with FixedHeader v4+. */
		.dtfh-floatingparent table.{{ $table->getName() }} th.{{ $field->getHtmlClassForCss() }}
		{
			min-width: var(--ib-column-width, {{ $width }})!important;
			max-width: var(--ib-column-width, {{ $width }})!important;
			width: var(--ib-column-width, {{ $width }})!important;
			box-sizing: border-box;
		}
    @endif

@endforeach

</style>

{{-- One replaceable stylesheet per column; inline width constraints are restored by the inspector. --}}
@foreach(($table->getDatatableUserData()->columnsSettings ?? []) as $name => $parameters)
    <style data-ib-column-style data-table-id="{{ $table->getId() }}" data-column-name="{{ $name }}">{!! \IlBronza\Datatables\Support\ColumnStyles::css($table->getId(), $name, $parameters['selectors'] ?? []) !!}</style>
@endforeach
