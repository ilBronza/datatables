@if($value->isAttachable())
<span 
	class="uk-button uk-button-primary uk-button-small table-action-button"
	data-table="dossier"
	data-route="{{ route('trips.dossiers.add', ['trip' => $value->id]) }}"
	>
	@lang('trips.attach')

</span>
@else
<span class="uk-text-muted" uk-icon="ban"></span>
@endif