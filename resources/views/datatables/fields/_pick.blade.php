<!-- START \views\tables\fields\_pick.blade.php -->

<a class="pickbutton" href="javascript:void(0)" data-id="{{ $value->id }}">
	<span uk-icon="link"></span>
	<span class="content">
		{{ datatable::getSubfieldValue($value, $field) }}
	</span>
</a>

<!-- END \views\tables\fields\_pick.blade.php -->
