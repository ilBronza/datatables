@foreach($value as $related)
	<a uk-tooltip title="{{ $element->getRelationEditUrl($related) }}" href="{{ $related->getShowUrl() }}">{{ $related->getNameForDisplayRelation() }}</a>
	@if(! $loop->last) - @endif
@endforeach