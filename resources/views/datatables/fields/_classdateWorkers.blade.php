<!-- START \views\tables\fields\_classdateWorkers.blade.php -->

@foreach($value->workers as $worker)
<div>
	<label>
		<input
			type="checkbox"
			name="worker[{{ $worker->pivot->id }}]"
			@if(!! $worker->pivot->confirmed)
			checked
			@endif

			value="{{ $worker->pivot->id }}"
			/>
		{{ $worker->user->name }}
	</label>
</div>
@endforeach

<!-- END \views\tables\fields\_classdateWorkers.blade.php -->