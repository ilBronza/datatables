<script type="text/javascript">
jQuery(document).ready(function()
{
    
    window.datatablesGetJsonObjectValues = function(key, fields, object)
    {
        let result = new Array();

        result.push(key + ': ' + object[key]);

        if(fields[key].length > 0)
            result.push(
                window.datatablesGetJsonObjectString(fields[key], object[key])
                );

        return result.join(', ');
    }


    window.datatablesGetJsonObjectString = function(fields, object)
    {
        let result = new Array();

        for (var key in fields)
            result.push(window.datatablesGetJsonObjectValues(key, fields, object));

        return result.join(' - ');
    }

    window.datatablesJsonEncode = function(object)
    {
        return JSON.stringify(object, null, 2);
    }

});
</script>