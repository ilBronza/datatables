<script type="text/javascript">


    window.{{ $table->getId() }}columnDefs = @if(count($table->columnDefs)||(count($table->customColumnDefs))) [

    @foreach ($table->columnDefs as $type => $element)
        @foreach($element->values as $key => $value)
        @if($key == '')
        {"{{ $type }}": false, "targets": {{ json_encode($value) }}},
        @elseif($key == 1)
        {"{{ $type }}": true, "targets": {{ json_encode($value) }}},
        @elseif($key == 'dom-text-numeric')
        {"{{ $type }}": "{{ $key }}", "sType" : "numeric", "targets": {{ json_encode($value) }}},
        @else
        {"{{ $type }}": "{{ $key }}", "targets": {{ json_encode($value) }}},
        @endif
        @endforeach
    @endforeach

    @foreach ($table->customColumnDefs as $customColumnDef)
        {!! $customColumnDef !!},
    @endforeach
    ]
@endif
;
</script>