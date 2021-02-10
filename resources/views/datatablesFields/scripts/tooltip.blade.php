<style type="text/css">
	
td.tooltip
{
	position: relative;
}

.tooltip .tooltipcontent
{
	position: absolute;
	top: -10px;
	left: 30px;
	height: 180px;
	overflow-y: scroll;
	z-index: 99999;
}

</style>

<script type="text/javascript">

jQuery(document).ready(function()
{
	$('body').on('mouseenter', 'td.tooltip', function()
	{
		if($(this).find('.tooltipcontent').length)
			return;

	    let content = $(this).children().html();

	    var div = "<div class='tooltipcontent uk-card uk-card-primary'>" + content + "</div>";

	    $(this).append(div);
	});	

	$('body').on('mouseleave', 'td.tooltip', function()
	{
		$(this).find('.tooltipcontent').remove();
	});

})

</script>