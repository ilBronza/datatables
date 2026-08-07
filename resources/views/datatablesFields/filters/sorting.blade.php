@if($table->hasSorting() && $field->isSortable())
<span class="changesorting header-sorting" role="button" tabindex="0" aria-label="Ordina per {{ $field->getTranslatedName() }}">
	<i class="fa-solid fa-sort" aria-hidden="true"></i>
</span>
@endif
