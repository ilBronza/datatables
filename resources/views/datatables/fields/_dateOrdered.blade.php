<!-- START \views\tables\fields\_date.blade.php -->

@if($value)
<span style="color: transparent; width: 1px;">{{ date(trans('dates.search'), strtotime($value)) }}</span>
{{ date(trans('dates.human'), strtotime($value)) }}
@endif

<!-- END \views\tables\fields\_date.blade.php -->
