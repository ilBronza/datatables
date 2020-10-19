<!-- START \views\tables\fields\__fastEdit.blade.php -->
@include('utilities.links._iframe', [
	'iframeIcon' => 'pencil',
	'iframeUrl' => iframed(editURL($value)),
	'iframeTitle' => trans('links.fasEdit')])
<!-- END \views\tables\fields\__fastEdit.blade.php -->
