@if(false)
<script type="text/javascript">
@endif

    //"rowCallback": function(row, data, index)

    var cellValue = data[{{ $field->absoluteIndex }}];
    var api = this.api();
    var node = api.cell(index, {{ $field->absoluteIndex }}).node();

    $(node).addClass(cellValue);
    $(node).addClass(index);
    console.log(index);

@if(false)
</script>
@endif

