<!-- START \views\tables\fields\_restore.blade.php -->

@include('utilities.links._targetBlankEmpty', [
	'linkTitle' => trans('utilities.restore'),
	'linkUrl' => route('restore' . class_basename($value), [singularClass($value) => $value->id]),
	'linkIcon' => 'history',
	'disappear' => true
	])

<!-- END \views\tables\fields\_restore.blade.php -->
