@if(isset($user)&&($user->isOwnerOf($value)))
<a href="{{ editURL($value) }}">E</a>
@endif