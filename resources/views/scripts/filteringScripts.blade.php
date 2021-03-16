{{-- START FILTERFUNCTIONS --}}
<style type="text/css">
    .datatablefilter
    {
        position: relative;
    }

    .filterfunctions
    {
        position: absolute;
        top: -14px;
        left: 0px;
        background-color: rgba(0, 0, 0, 0.5);
        color: #fff;
    }

    td
    {
        max-width: 140px;
    }

    nobr
    {
        display: inline-block;
        max-width: 100%;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
        line-height: 1em;
    }

    .uk-text-truncate br
    {
        display: none!important;
    }

    .uk-text-truncate *
    {
        display: inline!important;
    }
</style>

<script type="text/javascript">

$(document).ready(function($)
{
    $('body').on('mouseenter', '.datatablefilter', function()
    {
        $(this).find('.filterfunctions').removeClass('uk-hidden');
    });

    $('body').on('mouseleave', '.datatablefilter', function()
    {
        $(this).find('.filterfunctions').addClass('uk-hidden');
    });

    $('th .filterfunctions *').click(function(e)
    {
        e.stopPropagation();
    });

    $('.filterfunctions .close').click(function(e)
    {
        $(this).closest('.datatablefilter').find('input, select, textarea').each(function()
        {
            $(this).val('');
            $(this).change();
        });
    });

    $('th input').click(function(e)
    {
        e.stopPropagation();
    });

    $('input[type="date"]').change(function()
    {
        $(this).data('timestamp', new Date($(this).val()).getTime() / 1000);
    });
    
    window.normalFilter = function (container, section)
    {
        window._filter(container, section, true);
    }

    window.rangeFilter = function (container, section)
    {
        window._filter(container, section);
    }

    window._filter = function (container, section, searchValue = false)
    {
        $('input', section).on('keyup change clear', function ()
        {
            let value = $(this).val().toLowerCase();

            if($(this).attr('type') == 'date')
                value = new Date(value).getTime() / 1000;

            $(this).data('value', value);

            // if(container.search() !== value)
            // {
                if(searchValue)
                    container.search(this.value, true, false).draw();

                else
                    container.draw();
            // }
        });        
    }

});
    
</script>

{{-- END FILTERFUNCTIONS --}}