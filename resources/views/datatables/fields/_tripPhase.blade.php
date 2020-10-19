<!-- START \views\tables\fields\_tripPhase.blade.php -->

<input
	type="text"
	class="uk-input moving tripphase phase phase{{ $element->id }}"
	id="phase-{{ $element->id }}"
	name="phase[{{ $element->id }}]"
	placeholder="fields.phase{{ $element->id }}"
	autoComplete="off"
	data-originalvalue="{{ $element->phase }}"
	value="{{ $element->phase}}" />

{{-- 


<span class="uk-margin-right">{{ $value }}</span>
<div class="uk-button-group">
	<a class="uk-button uk-button-primary uk-button-small" uk-icon="minus" href="{{ route('trips.decrementPhase', ['trip' => $trip->id, 'dossierTrip' => $element->id]) }}"></a>
	<a class="uk-button uk-button-small" uk-icon="plus" href="{{ route('trips.incrementPhase', ['trip' => $trip->id, 'dossierTrip' => $element->id]) }}"></a>
</div>
 --}}
<!-- END \views\tables\fields\_tripPhase.blade.php -->
