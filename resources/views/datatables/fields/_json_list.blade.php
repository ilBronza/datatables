@php
$arrayValues = json_decode($value);
@endphp <ul class="uk-list">@if(is_array($arrayValues)) @foreach($arrayValues as $_value) <li>{{ $_value }}</li> @endforeach @endif</ul>