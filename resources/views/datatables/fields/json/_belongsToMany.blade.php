{@foreach($value as $related)"{{ $related->pivot->id }}" : {"id" : {{ $related->id }}, "name" : "{{ $related->getNameForDisplayRelation() }}"}@if(! $loop->last), @endif @endforeach}