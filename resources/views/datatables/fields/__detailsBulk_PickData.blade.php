<!-- START \views\tables\fields\__detailsBulk_PickData.blade.php -->

<button
	class="uk-button uk-button-small uk-button-secondary"
	type="submit"
	uk-icon="icon: edit"
	name="data[{{ $value->id }}]"
	uk-tooltip="" title="@lang('details.pickThisData')"
	>
	@lang('utilities.pick')
</button>

<!-- END \views\tables\fields\__detailsBulk_PickData.blade.php -->
