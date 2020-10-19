
{{-- Soluzione 1 with URL + GET --}}
<a href={{ route('detachDossierFromTrip', ['dossier' => $value->id, 'trip' => $trip->id ]) }}>
	<button class="uk-button uk-button-primary">Cancel</button>
</a>