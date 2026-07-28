<div class="ib-datatable-inline-create">
    @foreach ($table->getInlineEditFields() as $field)
        {!! $field->getInlineEditHtml() !!}
    @endforeach
</div>
