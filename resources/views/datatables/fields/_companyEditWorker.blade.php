<!-- START \views\tables\fields\_companyEditWorker.blade.php -->

@if(isset($manager))
<a href="{{ route('companies.workers.edit', ['company' => $company->id, 'worker' => $element->id]) }}">E</a>
@else
<a href="{{ route('company.workers.edit', ['worker' => $element->id]) }}">EDIT</a>
@endif

{{-- @include('utilities.links._iframe', [
	'iframeIcon' => 'pencil',
	'iframeUrl' => iframed(editURL($value)),
	'iframeTitle' => trans('links.fasEdit')])

 --}}<!-- END \views\tables\fields\_companyEditWorker.blade.php -->
