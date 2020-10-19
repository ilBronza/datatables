<!-- START \views\tables\fields\__fastDelete.blade.php -->

@section('scripts.header.delete')
<script type="text/javascript">

	alert('cucù');
/*
	jQuery(document).on('click','.button-delete',function(e)
	{
        e.stopPropagation();
		var that = this;

		if(confirm("@lang('messages.areYouSureToDeleteThisItem')"))
		{
			$.ajax({

				@if(!$value->trashed())
				url : '{{ route('deleteModelAjax') }}',
				@else
				url : '{{ route('forceDeleteModelAjax') }}',
				@endif

				type : 'DELETE',
				dataType : 'json',
				data : {
					type : $(that).data('type'),
					id : $(that).data('id'),
				},
				success : function(response)
				{
					if(response.success == true)
					{
						jQuery(that).closest('tr').fadeOut(150);
						// jQuery(that).closest('li').fadeOut(150);
					}

					else 
						alert('@lang('errors.elementNotDeleted')');
				},
				error : function(response, message, xhr)
				{
					if((response.responseJSON.message !== 'undefined')&&(response.responseJSON.message != ''))
						alert(response.responseJSON.message);
					else
						alert('@lang('errors.elementNotDeleted')');
				}
			});
		}
	});
*/
</script>
@endsection



<button data-type="{{ class_basename($value) }}" data-id="{{ $value->id }}" uk-tooltip="" title="@lang("links.deleteElement")" class="button-delete" type="button" uk-icon="icon: trash"></button>

<!-- END \views\tables\fields\__fastDelete.blade.php -->
