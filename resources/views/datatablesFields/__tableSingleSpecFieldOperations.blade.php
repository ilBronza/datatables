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
        $(this).css('position', 'relative');
        $(this).addClass('ibop');

        var div = "<div class='fieldoperations' style='width: {{ count($field->getFieldOperations()) * 30 }}px;'>@include('datatables::datatablesFields.___tableSingleSpecFieldOperation')</div>";

        $(this).append(div);
    });

   @endif

@endforeach

});

</script> 