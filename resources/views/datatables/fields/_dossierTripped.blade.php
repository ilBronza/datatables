<!-- START \views\tables\fields\_dossierTripped.blade.php -->

@if($trip = $value->first())

	<a href="{{ route('showTrip', ['trip' => $trip->id]) }}">{{ $trip->expiring_at? $trip->expiring_at->format(trans('generals.dateFormat')) : $trip->name}}</a>
@else


@if($element->company->getTrips()->count())
	@include('navbars.__listItemIframe', [
	    'label' => 'dossiers.pickAProgrammedTrip',
	    'href' => route('linkDossierToTrip', ['dossier' => $element->id]) . '?by-company=true',
	])
@endif

@include('navbars.__listItemIframe', [
    'label' => 'dossiers.pickATrip',
    'href' => route('linkDossierToTrip', ['dossier' => $element->id])
])

@endif

<!-- END \views\tables\fields\_dossierTripped.blade.php -->
