<div class="ib-datatable-inline-edit">
    @foreach ($table->getInlineEditFields() as $field)
        {!! $field->getInlineEditHtml() !!}
    @endforeach
</div>
