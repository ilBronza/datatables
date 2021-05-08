<!DOCTYPE html>
<html lang="{{ App::getLocale() }}">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">

	<script type="text/javascript">

	@if($postTableToUrl ?? false)
	window.parent.postTableToUrl("#{{ $callertablename }}", "{{ $postTableToUrl }}");
	@endif

	@if($callertablename = request()->input('callertablename', false))
	window.parent.reloadAjaxTable("#{{ $callertablename }}");
	window.parent.removePopup("#datatablepopup");
	@endif

	@if(isset($closeMessage))
	window.parent.addSuccessNotification('{{ $closeMessage }}');
	@endif

	// window.close();

</script>
</head>
<body></body>
</html>