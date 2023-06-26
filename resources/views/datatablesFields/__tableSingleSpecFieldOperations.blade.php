{{-- @foreach($table->getFields() as $field)
    @if($field->hasFieldOperations())
<h1>{{ json_encode($field->getFieldOperationsIcons()) }}</h1>
@endif
@endforeach
 --}}


<script type="text/javascript">

jQuery(document).ready(function()
{
@foreach($table->getFields() as $field)
    @if($field->hasFieldOperations())

    $('body').on('mouseenter', '#{{ $table->getId() }} td.{{ $field->getCamelName() }}', function()
    {
        $(this).css('position', 'relative');
        $(this).addClass('ibop');

        var div = "<div class='fieldoperations' style='width: {{ count($field->getFieldOperationsIcons()) * 30 }}px;'>@foreach($field->getFieldOperationsIcons() as $name => $icon)<span class='{{ $name }}' uk-icon='{{ $icon }}'></span>@endforeach</div>";

        $(this).append(div);
    });

   @endif

@endforeach

});

</script> 