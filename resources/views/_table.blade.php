@section('pageTitle')
    @if($caption = $table->getCaption())
    <span class="pagetitle">{{ $caption }}</span>
    @endif
@endsection

@include('datatables::datatablesFields._tableSingleSpec')
@include('datatables::__extraViews', ['position' => 'top'])


    <table
        id="{{ $table->getId() }}"
        data-url="{{ $table->getUrl() }}"
        data-cachedtablekey="{{ $table->getCachedDataKey() }}"

        @if($table->hasSummary())
        data-summary="true"
        @endif

        class="wannabedatatable {{ $table->getStripeClass() }} datatable {{ $table->getName() }} {{ session('lightTableMode', null) ? 'datatablelight' : '' }}"
        style="width:100%"
        >

        @if(! session('lightTableMode', false))
        <thead class="sectionheader">
            <tr class="columns">
                @foreach($table->getFields() as $field)
                <th

                    data-range="{{ $field->hasRangeFilter() }}"
                    data-filter="{{ $field->getFilterType() }}"
                    data-name="{{ $field->getFieldName() }}"
                    data-column="{{ $field->getIndex() }}"
                    class="{{ $field->getHtmlClass() }}"
                    >                    
                    {{ $field->renderHeader() }}
                </th>
                @endforeach
            </tr>

            @if($table->hasSummary())

            <tr class="summary" style="display: none;">
                @foreach($table->getFields() as $field)
                <th
                    class="summary{{ $field->getIndex() }}"
                    data-name="{{ $field->getFieldName() }}"
                    data-column="{{ $field->getIndex() }}"
                    >
                </th>
                @endforeach
            </tr>

                @if($table->hasInlineSearch())

            <tr class="inlinesearchsummary" style="display: none;">
                @foreach($table->getFields() as $field)
                <th
                    class="summary{{ $field->getIndex() }}"
                    data-name="{{ $field->getFieldName() }}"
                    data-column="{{ $field->getIndex() }}"
                    >
                </th>
                @endforeach
            </tr>

                @endif

            @endif


        </thead>
        @else

        <thead class="sectionheader">
            <tr class="columns">
                @foreach($table->getFields() as $field)
                <th
                    data-range="{{ $field->hasRangeFilter() }}"
                    data-filter="{{ $field->getFilterType() }}"
                    data-name="{{ $field->getFieldName() }}"
                    data-column="{{ $field->getIndex() }}"
                    class="{{ $field->getHtmlClass() }}"
                    >                    
                    {{ $field->renderHeader() }}
                </th>
                @endforeach
            </tr>
        </thead>

        @foreach($table->calculateData() as $index => $data)
        <tr>
            @foreach($data as $value)
            <td>
                @if(is_array($value))
                <a href="{{ $value[0] }}">{{ $value[1] }}</a>
                @else
                {!! $value !!}
                @endif
            </td>
            @endforeach
            
        </tr>
        @endforeach

        @endif



{{--
        <tfoot>
            <tr>
                @foreach($table->getFields() as $field)
                <th>{{ $field->renderHeader() }}</th>
                @endforeach
            </tr>
        </tfoot>
 --}}    

        </table>


@include('datatables::__extraViews', ['position' => 'bottom'])

