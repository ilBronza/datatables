<!-- START \views\tables\fields\_detailrowPopulate.blade.php -->

@include('details.rows.__' .  $value->getType(), [
	'model' => $value->detail,
	'rowName' => $value->getLabel(),
	'type' => $value->getType(),
	'row' => $value
	])

<!-- END \views\tables\fields\_detailrowPopulate.blade.php -->