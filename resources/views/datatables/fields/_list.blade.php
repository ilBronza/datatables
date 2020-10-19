<!-- START \views\tables\fields\_list.blade.php -->

<ul class="uk-list">
@foreach($value as $_value)
	<li>
		@foreach($types as $type)
			@foreach($column->subfields as $subfield)
			{{ $_value->$subfield }}
				@if(! $loop->last)
					-
				@endif
			@endforeach

		@endforeach
	</li>
@endforeach
</ul>

<!-- END \views\tables\fields\_list.blade.php -->
