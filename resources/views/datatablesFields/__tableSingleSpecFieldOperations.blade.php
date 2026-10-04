{{-- @foreach($table->getFields() as $field)
    @if($field->hasFieldOperations())
<h1>{{ json_encode($field->getFieldOperations()) }}</h1>
@endif
@endforeach

 --}}{{-- <pre>@include('datatables::datatablesFields.___tableSingleSpecFieldOperation')</pre> --}}

<script type="text/javascript">

jQuery(document).ready(function()
{
@foreach($table->getFields() as $field)
    @if($field->hasFieldOperations())

    $('body').on('mouseenter', '#{{ $table->getId() }} td.{{ $field->getCamelName() }}', function()
    {
        if($(this).children('.fieldoperations').length)
            return;

        $(this).css('position', 'relative');
        $(this).addClass('ibop');

        var div = $("<div class='fieldoperations' style='width: {{ count($field->getFieldOperations()) * 30 }}px;'>@include('datatables::datatablesFields.___tableSingleSpecFieldOperation')</div>");
        window.ibDtInitFieldOperations(div, $(this).closest('table.datatable').DataTable().cell(this).data(), @json($field->getEditorFieldOperations()));

        $(this).append(div);
    });

   @endif

    @if(isset($field->child) && $field->child->hasFieldOperations())
    $('body').on('mouseenter', '#{{ $table->getId() }} td.{{ $field->getCamelName() }} [data-cellindex]', function()
    {
        if($(this).children('.fieldoperations').length)
            return;

        $(this).css('position', 'relative');
        $(this).addClass('ibop');

        var div = $("<div class='fieldoperations' style='width: {{ count($field->child->getFieldOperations()) * 30 }}px;'>@include('datatables::datatablesFields.___tableSingleSpecFieldOperation', ['field' => $field->child])</div>");
        var item = window.__getCell(this).data()[$(this).attr('data-cellindex')];
        window.ibDtInitFieldOperations(div, item, @json($field->child->getEditorFieldOperations()));

        $(this).append(div);
    });
    @endif

@endforeach

});

</script>
