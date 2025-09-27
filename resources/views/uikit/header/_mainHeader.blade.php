        <tr class="mainheader">
            @foreach($table->getFields() as $field)
                @if($field->hasMainHeader())
                    <th class="{{ Str::slug($field->mainHeader['label']) }}"
                        colspan="{{ $colspan = ($field->mainHeader['colspan'] ?? 1) }}">
                        <span>{!! $field->mainHeader['label'] !!}</span>
                    </th>
                @else
                    @if(($colspan = (($colspan ?? 0) -1)) <= 0)
                        <th></th>
                    @endif
                @endif
            @endforeach
        </tr>