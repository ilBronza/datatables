@if($table->hasHeaderStylesToggleButton())
<style id="ib-header-styles-{{ $table->getId() }}" data-ib-header-styles-mode>
{!! \IlBronza\Datatables\Support\HeaderStyles::modeCss($table->getId(), $table->areHeaderStylesExpanded()) !!}
</style>
@endif
