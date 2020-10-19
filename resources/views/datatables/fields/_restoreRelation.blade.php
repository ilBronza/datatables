<!-- START \views\tables\fields\flat.blade.php -->

@include('utilities.links._targetBlankEmpty', [
	'linkTitle' => trans('utilities.restore'),
	'linkUrl' => url()->route('restore' . ucfirst(datatable::getSubfield($column->field)) . class_basename($value), [singularClass($value) => $value->id]),
	'linkIcon' => 'history',
	'disappear' => true
	])

<!-- END \views\tables\fields\flat.blade.php -->
