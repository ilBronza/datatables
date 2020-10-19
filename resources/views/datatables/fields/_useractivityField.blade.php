<!-- START \views\tables\fields\_useractivityField.blade.php -->
@php
	$newField = 'new' . $field;
	$oldField = 'old' . $field;
@endphp

<span class="old">{{ json_encode($element->$oldField) }}</span>
<br />
<span class="new">{{ json_encode($element->$newField) }}</span>

<!-- END \views\tables\fields\_useractivityField.blade.php -->
