{{ json_encode($column) }}







{{-- 
@if(isset($column->subfields)&&(count($column->subfields)))
	@foreach($column->subfields as $adminLink)
		@include('tables.fields.__' . $adminLink)
	@endforeach
@else
	@foreach($types as $adminLink)
		@include('tables.fields.__' . resolveAdminLink($adminLink))
	@endforeach
@endif
 --}}