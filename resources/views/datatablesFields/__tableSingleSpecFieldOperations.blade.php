<script type="text/javascript">

jQuery(document).ready(function()
{
    function setFieldOperationTitles(container, tooltips)
    {
        Object.entries(tooltips).forEach(function(entry)
        {
            container.find('a, span').filter(function()
            {
                return $(this).hasClass(entry[0]);
            }).attr('title', entry[1]);
        });
    }

@foreach($table->getFields() as $field)
    @if($field->hasFieldOperations())

    $('body').on('mouseenter', '#{{ $table->getId() }} td.{{ $field->getCamelName() }}', function()
    {
        if($(this).children('.fieldoperations').length)
            return;

        $(this).css('position', 'relative');
        $(this).addClass('ibop');

        @php($hasLabels = collect($field->getFieldOperations())->contains(fn ($operation) => filled($operation->getLabel())))
        @php($operationHtml = $__env->make('datatables::datatablesFields.___tableSingleSpecFieldOperation', ['field' => $field])->render())
        var div = $('<div/>', {'class': 'fieldoperations'})
            .toggleClass('fieldoperations-vertical', @json($field->fieldOperationsVertical))
            .css('width', @json($hasLabels ? 'max-content' : ($field->fieldOperationsVertical ? 1 : count($field->getFieldOperations())) * 30 . 'px'))
            .html(@json($operationHtml));
        @if($hasLabels)div.css({'white-space': 'nowrap', 'z-index': 10});@endif

        var datatable = $(this).closest('table.datatable').DataTable();
        var item = datatable.cell(this).data();
        @if($field->getType() === 'links.see')
        item = [datatable.row($(this).closest('tr')).id(), item];
        @endif
        window.ibDtInitFieldOperations(div, item, @json($field->getEditorFieldOperations()));
        setFieldOperationTitles(div, @json(collect($field->getFieldOperations())->map(fn ($operation) => $operation->getTooltip())));

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

        @php($hasLabels = collect($field->child->getFieldOperations())->contains(fn ($operation) => filled($operation->getLabel())))
        @php($operationHtml = $__env->make('datatables::datatablesFields.___tableSingleSpecFieldOperation', ['field' => $field->child])->render())
        var div = $('<div/>', {'class': 'fieldoperations'})
            .toggleClass('fieldoperations-vertical', @json($field->child->fieldOperationsVertical))
            .css('width', @json($hasLabels ? 'max-content' : ($field->child->fieldOperationsVertical ? 1 : count($field->child->getFieldOperations())) * 30 . 'px'))
            .html(@json($operationHtml));
        @if($hasLabels)div.css({'white-space': 'nowrap', 'z-index': 10});@endif
        var item = window.__getCell(this).data()[$(this).attr('data-cellindex')];
        window.ibDtInitFieldOperations(div, item, @json($field->child->getEditorFieldOperations()));
        setFieldOperationTitles(div, @json(collect($field->child->getFieldOperations())->map(fn ($operation) => $operation->getTooltip())));

        $(this).append(div);
    });
    @endif

@endforeach

});

</script>
