<!-- START \views\tables\fields\__seeMini.blade.php -->

@include('utilities.links._mini', [
	'miniUrl' => iframed($iframeUrl?? showURL($value)),
	'miniIcon' => 'info'
	])

<!-- END \views\tables\fields\__seeMini.blade.php -->
