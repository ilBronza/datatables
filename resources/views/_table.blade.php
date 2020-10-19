@include('datatables::datatablesFields._columnDefs')

    <table
        id="{{ $table->getId() }}"
        data-url="{{ $table->getUrl() }}"
        data-cachedtablekey="{{ $table->getCachedDataKey() }}"

        @if($table->hasSummary())
        data-summary="true"
        @endif

        class="wannabedatatable"
        style="width:100%"
        >
        <thead>
            <tr class="columns">
                @foreach($table->getFields() as $field)
                <th
                    data-name="{{ $field->getFieldName() }}"
                    data-column="{{ $field->getIndex() }}"
                    >
                    {{ $field->renderHeader() }}
                </th>
                @endforeach
            </tr>

            @if($table->hasSummary())

            <tr class="summary">
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

            <tr class="inlinesearchsummary">
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
{{--         <tfoot>
            <tr>
                @foreach($table->getFields() as $field)
                <th>{{ $field->renderHeader() }}</th>
                @endforeach
            </tr>
        </tfoot>
 --}}    </table>

