        <tr class="mainheader" data-dt-order="disable">
            @foreach($table->getFields() as $field)
                @if($field->hasMainHeader())
                    @php($label = is_array($field->mainHeader) ? $field->mainHeader['label'] : $field->mainHeader)
                    <th class="{{ Str::slug($label) }}"
                        @if($color = \IlBronza\Datatables\Support\ColumnStyles::fieldBackgroundColor($field->mainHeader['backgroundColor'] ?? null)) style="background-color: {{ $color }}" @endif
                        colspan="{{ $colspan = ($field->mainHeader['colspan'] ?? 1) }}">
                        <span>{!! $label !!}</span>
                    </th>
                @else
                    @if(($colspan = (($colspan ?? 0) -1)) <= 0)
                        <th></th>
                    @endif
                @endif
            @endforeach
        </tr>
