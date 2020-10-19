<!-- START \views\tables\fields\__see.blade.php -->

<a
	uk-tooltip="" title="@lang('utilities.open')"
	href="{{ url()->route('destroyManagerCompany', ['manager' => $parentModel->id, 'company' => $value->id]) }}">
	<span
		uk-icon="icon: ban"
		></span></a>

<!-- END \views\tables\fields\__see.blade.php -->
