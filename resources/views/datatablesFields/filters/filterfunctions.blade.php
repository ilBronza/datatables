<div class="filterfunctions uk-flex uk-flex-middle uk-hidden">
	@if($table->hasSorting() && $field->isSortable())
	<span class="changesorting"><i class="fa-solid fa-sort"></i></span>
	@endif
	<span class="removefiltercontent"><i class="fa-solid fa-x"></i></span>

	@if($field->canBeHidden())
	 <span class="hidecolumn"><i class="fa-solid fa-eye"></i></span>
	@endif
</div>
