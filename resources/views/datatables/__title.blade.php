@if($table->mustShowTitle())
<div class="uk-width-expand">
	{{ trans('tables.title' . ($table->title?? $table->name)) }}
</div>
@endif