@php
$listName = $field->listVariable;
@endphp <select name="{{ $field->fieldname }}[{{ $element->id }}]"> <option></option> @foreach($$listName as $key => $text) @if($key == $value)<option selected value="{{ $key }}">{{ $text }}</option>@else <option value="{{ $key }}">{{ $text }}</option>@endif @endforeach </select>