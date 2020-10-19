<!-- START \views\tables\fields\_consultDate.blade.php -->

@if($value)
<span>{{ date(trans('fields.consultDateFormat'), strtotime($value)) }}</span>
@endif

<!-- END \views\tables\fields\_consultDate.blade.php -->
