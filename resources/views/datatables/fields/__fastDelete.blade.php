<a
@if(!$value->trashed())
href="{{ $value->getDeleteUrl() }}"
@else
href="{{ $value->getForceDeleteUrl() }}"
@endif
class="trash">&nbsp;</a>