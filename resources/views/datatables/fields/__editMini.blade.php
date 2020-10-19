<!-- START \views\tables\fields\__editMini.blade.php -->

@include('utilities.links._mini', [
	'miniUrl' => iframed($iframeUrl?? editURL($value)),
	'miniIcon' => 'file-edit'
	])

<!-- END \views\tables\fields\__editMini.blade.php -->
