<div class="ib-datatable-inline-edit uk-grid-small" uk-grid>
    @foreach ($table->getInlineEditFields() as $field)
        <div class="uk-width-1-2@s">
            <label class="uk-form-label">{{ $field->getTranslatedName() }}</label>

            <div class="uk-form-controls">
                {!! $field->getInlineEditHtml() !!}
            </div>
        </div>
    @endforeach
</div>
