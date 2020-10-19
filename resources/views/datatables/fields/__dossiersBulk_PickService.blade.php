<!-- START \views\tables\fields\__dossiersBulk_PickService.blade.php -->

<button
	class="uk-button uk-button-small uk-button-secondary"
	type="submit"
	uk-icon="icon: edit"
	name="service[{{ $value->id }}]"
	uk-tooltip="" title="@lang('dossiers.pickThisService')"
	>
	@lang('utilities.pick')
</button>

<!-- END \views\tables\fields\__dossiersBulk_PickService.blade.php -->
