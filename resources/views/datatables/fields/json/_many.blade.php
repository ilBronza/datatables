@foreach($value as $element)
	<a href="{{ $element->getShowUrl() }}">{{ $element->getNameForDisplayRelation() }}</a>
	@if(! $loop->last) - @endif
@endforeach