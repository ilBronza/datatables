@if($extraViews = $table->getExtraViews($position))
    @foreach($extraViews as $extraView)
    {!! $extraView !!}
    @endforeach
@endif