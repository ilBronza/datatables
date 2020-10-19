@foreach($value as $worker)
<label>
	<input type="checkbox" value="{{ $worker->id }}" name="worker[{{ $worker->id }}]" />
	{{ $worker->user->name }}
</label>
@endforeach
