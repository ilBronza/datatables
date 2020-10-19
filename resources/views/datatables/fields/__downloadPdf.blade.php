<!-- START \views\tables\fields\__downloadPdf.blade.php -->

@if($value->hasPdfMethod())
<a target="_blank" href="{{ $value->getPdfUrl() }}" uk-icon="cloud-download">&nbsp;</a>
@elseif($value->producePdf())
<a target="_blank" href="{{ $value->getPdfUrl() }}" uk-icon="cloud-download">&nbsp;</a>
@endif

<!-- END \views\tables\fields\__downloadPdf.blade.php -->
