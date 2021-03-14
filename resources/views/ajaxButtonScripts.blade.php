<style type="text/css">
.ib-toggle
{
    cursor: pointer;
}

.ib-spin
{
    position: absolute;
    left: 4px;
    top: 3px;
    background-color: rgba(2, 122, 32, 0.7)!important;
    color: #fff;
}
</style>

<script type="text/javascript">

$(document).ready(function($)
{
    function __addSpinner(target)
    {
        let td = $(target).parents('td');

        td.css('position', 'relative');
        td.append("<div class='ib-spin' uk-spinner='ratio: 0.5'></div>");
    }

    function __addSpinnerIcon(target)
    {
        target.prepend("<div uk-spinner></div>");
    }

    function __removeSpinner(target)
    {
        $(target).find('div.ib-spin').remove();
    }

    function removeSpinner(target)
    {
        target.find('div.ib-spin').remove();
    }

    function checkFinalJavascript(result, that)
    {
        if (typeof result.finalJavascript !== 'undefined')
            eval(result.finalJavascript);
    }

    function checkNewselecttor(result)
    {
        if (typeof result.newselectto !== 'undefined')
            $('.selectto').select2();
    }

    function checkRemoveClass(result, target)
    {
        if (typeof result.removeClass !== 'undefined')
            target.removeClass(result.removeClass);
    }

    function checkTooltip(result, target)
    {
        if (typeof result.tooltip !== 'undefined')
            target.attr('title', result.tooltip);

        UIkit.tooltip(target);
    }

    function checkDatas(result, target)
    {
        if (typeof result.datas !== 'undefined')
            target.data('datas', result.datas);
    }

    function checkIcon(result, target)
    {
        if (typeof result.icon !== 'undefined')
            target.attr('uk-icon', "icon: " + result.icon);

        UIkit.icon(target);
    }

    function checkClass(result, target)
    {
        if (typeof result.class !== 'undefined')
            target.addClass(result.class);
    }

    function checkUrl(result, target)
    {
        if (typeof result.url !== 'undefined')
            target.data('url', result.url);
    }

    function checkHtml(result, target)
    {
        if (typeof result.targetHtml === 'undefined')
            if (typeof result.targetHtmlAppend === 'undefined')
                if (typeof result.newTargetHtmlAppend === 'undefined')
                    if (typeof result.html !== 'undefined')
                        target.html(result.html);
    }

    function checkTargetHtml(result, target)
    {
        if (typeof result.targetHtml !== 'undefined')
            jQuery('#' + result.targetHtml).html(result.html);
    }

    function newCheckTargetHtmlAppend(result, target)
    {
        if (typeof result.newTargetHtmlAppend !== 'undefined')
        {
            jQuery(result.newTargetHtmlAppend).append(result.html);

            //ma che è sta roba
            jQuery(result.newTargetHtmlAppend).find("script").each(function()
            {
                console.log(jQuery(this).text());
            })
            console.log("script descript");
            console.log(jQuery(result.targetHtmlAppend).html());

            return true;
        }
        else return false;
    }

    function checkTargetHtmlAppend(result, target)
    {
        if (typeof result.targetHtmlAppend !== 'undefined')
        {
            jQuery('#' + result.targetHtmlAppend).append(result.html);

            jQuery('#' + result.targetHtmlAppend).find("script").each(function()
            {
                console.log(jQuery(this).text());
            })

            console.log("script descript");

            console.log(jQuery('#' + result.targetHtmlAppend).html());

            // eval($('#' + result.targetHtmlAppend).find("script").text());

        }
    }

    function checkMessage(result)
    {
        if (typeof result.message !== 'undefined')
        {
            if ((typeof result.success !== 'undefined')&&(result.success == true))
                        window.addSuccessNotification(result.message);
            else
                        window.addDangerNotification(result.message);

        }
    }

    function checkRemoveElement(result, target)
    {
        if (typeof result.remove !== 'undefined')
        {
            if(result.remove === true)
            {
                $(target).parents('table').DataTable().row($(target).closest('tr')).remove().draw();
                // console.log('gestire rimozione da array data');
                // $(target).closest('tr').fadeOut();
            }

            else 
                $(target).closest(result.remove).fadeOut();
        }
    }

    function collectDatas(target)
    {
        var elements = $(target).data('elements');
        var namedElements = $(target).data('namedelements');

        var result = {};

        if(typeof (namedElements) !== 'undefined')
        {
            elements.forEach(function(name)
            {

                var elementName = $('#' + name).attr('name');
                result[elementName] = $('#' + name).val();
            });
        }
        else
        {
            elements.forEach(function(name)
            {
                result[name] = $('#' + name).val();
            });
        }

        return result;
    }

    function collectDataAttributes(target)
    {
        var elements = $(target).data('dataattributes');

        var result = {};

        elements.forEach(function(data)
        {
            result[data] = $(target).data(data);
        });

        return result;
    }

    function addSpinner(target)
    {
        target.prepend("<div class='ib-spin' uk-spinner></div>");
    }

    function addSpinnerIcon(target)
    {
        target.prepend("<div uk-spinner></div>");
    }

    function checkJavascript(result, that)
    {
        if (typeof result.javascript !== 'undefined')
        {
            console.log('evaluo');
            eval(result.javascript);
        }
    }

    function __isEnabled(params)
    {
        if(($(params.target).hasClass('disabled'))||($(params.target).is(':disabled')))
            return false;

        return true;
    }

    function __isConfirmed(params)
    {
        var returnconfirm = $(params.target).data('returnconfirm');

        if (typeof returnconfirm !== 'undefined')
            if(! confirm(returnconfirm))
                return false;

        return true;
    }

    function __manageSpinner(params)
    {
        if($(params.target).hasClass('spin') == true)
            __addSpinner(params.target);

        if($(params.target).hasClass('spinicon') == true)
            __addSpinnerIcon(params.target);        
    }

    function __manageSpinnerRemoval(params)
    {
        if($(params.target).hasClass('spin') == true)
            __removeSpinner(params.target);
    }

    function __getData(params)
    {
        // if($(params.target).data('dataattributes'))
        //     return collectDataAttributes(params.target);

        // if($(params.target).data('elements'))
        //     return collectDatas(params.target);

        return params.data;
    }

    function __getType(params)
    {
        if($(params.target).data('type'))
            return $(params.target).data('type');        

        return 'POST';
    }

    function __getUrl(params)
    {
        return $(params.target).data('url');
    }

    function __addMethodToData(data, params)
    {
        if($(params.target).data('method'))
            data._method = $(params.target).data('method');

        return data;
    }

    function __checkResultToggle(result, params)
    {
        if(typeof result.toggle === "undefined")
            return;

        let table = $(params.target).parents('table.datatable').DataTable();
        let td = $(params.target).parents('td');
        let cell = table.cell(td);

        cellData = cell.data();

        let fieldName = params.data.field;

        cellData[1] = result[fieldName];
        cell.data(cellData).draw();
    }

    function ibCallAjax(params)
    {
        params.e.preventDefault();

        if(! __isEnabled(params))
            return false;

        if(! __isConfirmed(params))
            return false;

        __manageSpinner(params);

        var type = __getType(params);
        var url = __getUrl(params);
        let data = __getData(params);

        data = __addMethodToData(data, params);

        $.ajax(
        {
            url     : url,
            type    : type,
            dataType: 'json',
            data    : data,
            success : function(result)
            {
                __checkResultToggle(result, params);


                // checkJavascript(result, params.target);
                // checkRemoveElement(result, params.target);
                // checkMessage(result);

                // if(! newCheckTargetHtmlAppend(result, params.target))
                //     checkTargetHtmlAppend(result, params.target);

                // checkTargetHtml(result, params.target);

                // if(! newCheckTargetHtml(result, params.target))
                //     checkHtml(result, params.target);

                // checkUrl(result, params.target);
                // checkClass(result, params.target);
                // checkIcon(result, params.target);
                // checkDatas(result, params.target);
                // checkRemoveClass(result, params.target);
                // checkTooltip(result, params.target);
                // checkNewselecttor(result, params.target);
                // checkFinalJavascript(result, params.target);

                __manageSpinnerRemoval(params);

                console.log(result);
            },
            error   : function()
            {
                __manageSpinnerRemoval(params);
            }
        });

    }

    $('body').on('click', '.ib-toggle', function(e)
    {
        var params = {
            target : this,
            e : e,
            type : 'POST',
            data : {
                toggle : true,
                field : $(this).data('field'),
                _method : 'PUT',                
            }
        };

        ibCallAjax(params);
    });

    $('body').on('click', '.ib-ajax-button', function(e)
    {
        e.preventDefault();

        if(($(this).hasClass('disabled'))||($(this).is(':disabled')))
            return false;

        var returnconfirm = $(this).data('returnconfirm');
        if (typeof returnconfirm !== 'undefined')
            if(! confirm(returnconfirm))
                return false;

        var that = $(this);

        if($(that).hasClass('spin') == true)
            addSpinner(that);

        if($(that).hasClass('spinicon') == true)
            addSpinnerIcon(that);

        var data = $(that).data('datas');
        var type = 'POST';
        var url = that.data('url');


        if($(that).data('dataattributes'))
        {
            data = collectDataAttributes(that);
            type = 'POST';
        }
        else if($(that).data('elements'))
        {
            data = collectDatas(that);
            type = 'POST';
        }

        if($(that).data('type'))
            type = $(that).data('type');

        if($(that).data('dataattributes'))
            var sendingData = data;
        else
            var sendingData = {data : data};

        if($(that).data('method'))
            sendingData._method = $(that).data('method');

        $.ajax(
        {
            url     : url,
            type    : type,
            dataType: 'json',
            data    : sendingData,
            success : function(result)
            {
                checkJavascript(result, that);
                checkRemoveElement(result, that);
                checkMessage(result);

                if(! newCheckTargetHtmlAppend(result, that))
                    checkTargetHtmlAppend(result, that);

                checkTargetHtml(result, that);

                if(! newCheckTargetHtml(result, that))
                    checkHtml(result, that);

                checkUrl(result, that);
                checkClass(result, that);
                checkIcon(result, that);
                checkDatas(result, that);
                checkRemoveClass(result, that);
                checkTooltip(result, that);
                checkNewselecttor(result, that);
                checkFinalJavascript(result, that);

                if($(that).hasClass('spin') == true)
                    removeSpinner(that);

                console.log(result);
            },
            error   : function()
            {
                if($(that).hasClass('spin') == true)
                    removeSpinner(that);
            }
        });

    });



});
    
</script>