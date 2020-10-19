<!-- START \views\tables\fields\__createData.blade.php -->
<a target="_blank" class="uk-button uk-button-small" 
	href="{{ route('createByCompanyData', [
	'data' => (class_basename($element) == 'Data')? $element->id : $element->data->id,
	'company' => $dossier->company_id?? $element->company_id
	]) }}">@lang('generals.create')</a>
<!-- END \views\tables\fields\__createData.blade.php -->
