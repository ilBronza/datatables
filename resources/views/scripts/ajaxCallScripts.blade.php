<script type="text/javascript">

$(document).ready(function($)
{
    window.__mustOpenIframe = function(target)
    {
        if($(target).data('openiframe'))
            return true;

        return false;
    }

    window.__getDataTableSelectedIds = function(target, datatable)
    {
        let idColumnIndex = window.__getIdColumnIndex(target, datatable);
        let selectedRows = datatable.rows( { selected: true } );
        var ids = selectedRows.data().pluck(idColumnIndex).toArray();

        return ids;
    }

    window.__openIframe = function(target)
    {
        let table = window.__getTable(target);
        let tableId = $(table).attr('id');

        let datatable = table.DataTable();

        let ids = window.__getDataTableSelectedIds(target, datatable);

        let url = window.__getTargetUrl(target);
        let method = window.__getTargetMethod(target);
        let iframed = true;

        let data = {
            callertablename: tableId,
            ids: ids
        };

        window.openModalWithData (
            url,
            data,
            method,
            iframed
        )
    }

    window.postTableToUrl = function(tableId, url)
    {
        let datatable = $(tableId).DataTable();
        let ids = window.__getDataTableSelectedIds(null, datatable);
        let type = 'POST';

        let data = {
            ids: ids
        };

        var params = {
            target : datatable,
        };

        return window.__ibCallAjax(url, type, data, params);
    }

    window.reloadAjaxTable = function(tableId)
    {
        let table = $(tableId).DataTable();

        table.rows().deselect();
        table.ajax.reload();
    }

    window.removePopup = function(popupId)
    {
        UIkit.util.on(popupId, 'hide', function () {
            setTimeout(function()
            {
                $(popupId).remove();
            }, 1000);
        });

        UIkit.modal($(popupId)).hide();
    }

    window.openModalWithData = function (url, data, type = 'POST', iframed = true)
    {
        $('body').append('<div id="datatablepopup" uk-modal><div class="uk-modal-dialog uk-modal-body uk-width-1-1 uk-height-1-1"><h2 class="uk-modal-title"></h2><button class="uk-modal-close" type="button"></button><iframe id="modelpopupiframe" name="modelpopupiframe" class="uk-width-1-1 uk-height-1-1"></iframe></div></div>');

        UIkit.modal($('#datatablepopup')).show();

        var form = document.createElement("form");
        form.target = "modelpopupiframe";
        form.method = type;
        form.action = url;
        form.style.display = "none";

        for (var key in data) {
            var input = document.createElement("input");
            input.type = "hidden";
            input.name = key;
            input.value = data[key];
            form.appendChild(input);
        }

        if(iframed)
        {
            var input = document.createElement("input");
            input.type = "hidden";
            input.name = "iframed";
            input.value = true;
            form.appendChild(input);            
        }

        var input = document.createElement("input");
        input.type = "hidden";
        input.name = "_token";
        input.value = "{{ csrf_token() }}";
        form.appendChild(input);

        document.body.appendChild(form);
        form.submit();
        document.body.removeChild(form);
    }

    window.__getTR = function(target)
    {
        return $(target).parents('tr');
    }

    window.__getTD = function(target)
    {
        return $(target).parents('td');
    }

    window.__getTH = function(target)
    {
        var tdIndex = $(target).closest('td').index() + 1;

        return $(target).parents('table.dataTable').find('thead tr th:nth-child(' + tdIndex + ')');
    }

    function __addSpinner(target)
    {
        let td = window.__getTD(target);

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

    // function removeSpinner(target)
    // {
    //     target.find('div.ib-spin').remove();
    // }

    // function checkFinalJavascript(result, that)
    // {
    //     if (typeof result.finalJavascript !== 'undefined')
    //         eval(result.finalJavascript);
    // }

    // function checkNewselecttor(result)
    // {
    //     if (typeof result.newselectto !== 'undefined')
    //         $('.selectto').select2();
    // }

    // function checkRemoveClass(result, target)
    // {
    //     if (typeof result.removeClass !== 'undefined')
    //         target.removeClass(result.removeClass);
    // }

    // function checkTooltip(result, target)
    // {
    //     if (typeof result.tooltip !== 'undefined')
    //         target.attr('title', result.tooltip);

    //     UIkit.tooltip(target);
    // }

    // function checkDatas(result, target)
    // {
    //     if (typeof result.datas !== 'undefined')
    //         target.data('datas', result.datas);
    // }

    // function checkIcon(result, target)
    // {
    //     if (typeof result.icon !== 'undefined')
    //         target.attr('uk-icon', "icon: " + result.icon);

    //     UIkit.icon(target);
    // }

    // function checkClass(result, target)
    // {
    //     if (typeof result.class !== 'undefined')
    //         target.addClass(result.class);
    // }

    // function checkUrl(result, target)
    // {
    //     if (typeof result.url !== 'undefined')
    //         target.data('url', result.url);
    // }

    // function checkHtml(result, target)
    // {
    //     if (typeof result.targetHtml === 'undefined')
    //         if (typeof result.targetHtmlAppend === 'undefined')
    //             if (typeof result.newTargetHtmlAppend === 'undefined')
    //                 if (typeof result.html !== 'undefined')
    //                     target.html(result.html);
    // }

    // function checkTargetHtml(result, target)
    // {
    //     if (typeof result.targetHtml !== 'undefined')
    //         jQuery('#' + result.targetHtml).html(result.html);
    // }

    // function newCheckTargetHtmlAppend(result, target)
    // {
    //     if (typeof result.newTargetHtmlAppend !== 'undefined')
    //     {
    //         jQuery(result.newTargetHtmlAppend).append(result.html);

    //         //ma che è sta roba
    //         jQuery(result.newTargetHtmlAppend).find("script").each(function()
    //         {
    //             console.log(jQuery(this).text());
    //         })
    //         console.log("script descript");
    //         console.log(jQuery(result.targetHtmlAppend).html());

    //         return true;
    //     }
    //     else return false;
    // }

    // function checkTargetHtmlAppend(result, target)
    // {
    //     if (typeof result.targetHtmlAppend !== 'undefined')
    //     {
    //         jQuery('#' + result.targetHtmlAppend).append(result.html);

    //         jQuery('#' + result.targetHtmlAppend).find("script").each(function()
    //         {
    //             console.log(jQuery(this).text());
    //         })

    //         console.log("script descript");

    //         console.log(jQuery('#' + result.targetHtmlAppend).html());

    //         // eval($('#' + result.targetHtmlAppend).find("script").text());

    //     }
    // }

    // function checkMessage(result)
    // {
    //     if (typeof result.message !== 'undefined')
    //     {
    //         if ((typeof result.success !== 'undefined')&&(result.success == true))
    //                     window.addSuccessNotification(result.message);
    //         else
    //                     window.addDangerNotification(result.message);

    //     }
    // }

    // function checkRemoveElement(result, target)
    // {
    //     if (typeof result.remove !== 'undefined')
    //     {
    //         if(result.remove === true)
    //         {
    //             $(target).parents('table').DataTable().row($(target).closest('tr')).remove().draw();
    //             // console.log('gestire rimozione da array data');
    //             // $(target).closest('tr').fadeOut();
    //         }

    //         else 
    //             $(target).closest(result.remove).fadeOut();
    //     }
    // }

    // function collectDatas(target)
    // {
    //     var elements = $(target).data('elements');
    //     var namedElements = $(target).data('namedelements');

    //     var result = {};

    //     if(typeof (namedElements) !== 'undefined')
    //     {
    //         elements.forEach(function(name)
    //         {

    //             var elementName = $('#' + name).attr('name');
    //             result[elementName] = $('#' + name).val();
    //         });
    //     }
    //     else
    //     {
    //         elements.forEach(function(name)
    //         {
    //             result[name] = $('#' + name).val();
    //         });
    //     }

    //     return result;
    // }

    // function collectDataAttributes(target)
    // {
    //     var elements = $(target).data('dataattributes');

    //     var result = {};

    //     elements.forEach(function(data)
    //     {
    //         result[data] = $(target).data(data);
    //     });

    //     return result;
    // }

    // function checkJavascript(result, that)
    // {
    //     if (typeof result.javascript !== 'undefined')
    //     {
    //         console.log('evaluo');
    //         eval(result.javascript);
    //     }
    // }

    function __isEnabled(params)
    {
        if(($(params.target).hasClass('disabled'))||($(params.target).is(':disabled')))
            return false;

        return true;
    }

    window.__isHeaderConfirmed = function(params)
    {
        let th = window.__getTH(params.target);


        var confirmMessage = th.data('confirm');

        if (typeof confirmMessage !== 'undefined')
            if(! confirm(confirmMessage))
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

    window.__getTargetMethod = function(target)
    {
        if($(target).data('method'))
            return $(target).data('method');

        return 'POST';
    }

    function __getParametersMethod(parameters)
    {
        return window.__getTargetMethod(parameters.target);
    }

    function __getUrl(params)
    {
        if($(params.target).data('url'))
            return $(params.target).data('url');
        
        if($(params.target).attr('href'))
            return $(params.target).attr('href');
        
        alert('missing url for this element');
    }

    window.__getTargetUrl = function(target)
    {
        if($(target).data('url'))
            return $(target).data('url');
        
        if($(target).data('route'))
            return $(target).data('route');
        
        if($(target).attr('href'))
            return $(target).attr('href');
        
        alert('missing url for this element');
    }

    function __addMethodToData(data, params)
    {
        if($(params.target).data('method'))
            data._method = $(params.target).data('method');

        return data;
    }

    window.__checkResultGeneric = function(result, params)
    {
        let table = $(params.target).parents('table.datatable').DataTable();
        let td = $(params.target).parents('td');
        let cell = table.cell(td);

        cellData = cell.data();

        let fieldName = params.data.field;

        cellData[1] = result[fieldName];

        cell.data(cellData).draw();

        td.addClass('updated');
    }

    window.__getDataTable = function(target)
    {
        let tableId;

        if($(target).data('tableid'))
            tableId = $(target).data('tableid');

        else
            tableId = $(target).parents('table.datatable').attr('id');

        //this is because some elements contains # in the parameter string
        tableId = tableId.replace(/\b\#\w+/g, ''); 

        return $('#' + tableId).DataTable();
    }

    window.__getTable = function(target)
    {
        let tableId;

        if($(target).data('tableid'))
            tableId = $(target).data('tableid');

        else
            tableId = $(target).parents('table.datatable').attr('id');

        tableId = tableId.replace(/\b\#\w+/g, ''); 

        return $('#' + tableId);
    }

    window.__checkResultToggle = function(result, params)
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

        return true;
    }

    window.__reloadTable = function(params)
    {
        let table = window.__getDataTable(params.target);

        table.rows().deselect();
        table.ajax.reload();

        return true;
    }

    window.__refreshRow = function(params)
    {
        let table = window.__getDataTable(params.target);
        let row = window.__getTR(params.target);
        let rowId = $(row).attr('id');

        let url = table.ajax.url();

        $.ajax({
            dataType: "json",
            url: url,
            data: {
                ibeditor: true,
                rowId: rowId
            },
            success: function(response)
            {
                table.row("#" + rowId).data(response.data[0]).invalidate();
            },
            error: function()
            {
                console.log(response);
                alert('ricaricare la pagina');

                return false;
                // location.reload();
            }
        });

        return true;
    }

    window.__checkResultPopup = function(response, params)
    {
        if(response.ibaction == 'openPopup')
            return window.__openPopupOLDRIFARE(response, params);

    }

    window.__checkResultAction = function(response, params)
    {
        if(typeof response.ibaction === "undefined")
            return false;

        if(response.action == 'refreshRow')
            return window.__refreshRow(params);

        if(response.action == 'reloadTable')
            return window.__reloadTable(params);

        if(response.ibaction == 'openPopup')
            return window.__openPopupOLDRIFARE(response, params);

        return false;
    }

    window.__openPopupOLDRIFARE = function (response, params)
    {
        let table = window.__getTable(params.target);
        let tableId = $(table).attr('id');

        window.openModalWithData(
            response.route,
            {
                callertablename: tableId,
                target: params.target
            },
            'GET'
        );
    }

    window.__manageRowsRemoving = function(response, params)
    {
        if(response.action == 'remove')
        {
            let table = window.__getDataTable(params.target);
            let reload = false;

            response.ids.forEach(function(value)
            {
                if($(table.row("#" + value).node()).length == 0)
                    reload = true;

                $(table.row("#" + value).node()).fadeOut(
                    800, function()
                    {
                        table.row("#" + value).remove();
                    });
            });

            if(reload)
                table.ajax.reload();
            
            else
                table.draw();

            return true;
        }

        return false;
    }

    window.__ibCallAjax = function(url, type, data, params)
    {
        $.ajax(
        {
            url     : url,
            type    : type,
            dataType: 'json',
            data    : data,
            success : function(response)
            {
                console.log('son qua');
                console.log(response);

                if(! __checkResultToggle(response, params))
                    if(! __checkResultAction(response, params))
                        if(! window.__manageRowsRemoving(response, params))
                            __checkResultGeneric(response, params);

                // checkJavascript(response, params.target);
                // checkRemoveElement(response, params.target);
                // checkMessage(response);

                // if(! newCheckTargetHtmlAppend(response, params.target))
                //     checkTargetHtmlAppend(response, params.target);

                // checkTargetHtml(response, params.target);

                // if(! newCheckTargetHtml(response, params.target))
                //     checkHtml(response, params.target);

                // checkUrl(response, params.target);
                // checkClass(response, params.target);
                // checkIcon(response, params.target);
                // checkDatas(response, params.target);
                // checkRemoveClass(response, params.target);
                // checkTooltip(response, params.target);
                // checkNewselecttor(response, params.target);
                // checkFinalJavascript(response, params.target);

                __manageSpinnerRemoval(params);
                __manageSuccessMessages(response);
            },
            error: function(response)
            {
                console.log(response);
                __displayResponseErrors(response);
                __manageSpinnerRemoval(params);
            }
        });

    }

    window.ibCallAjax = function(params)
    {
        params.e.preventDefault();

        if(! __isEnabled(params))
            return false;

        if(! __isConfirmed(params))
            return false;

        if(! __isHeaderConfirmed(params))
            return false;

        __manageSpinner(params);

        var type = __getType(params);
        var url = __getUrl(params);
        let data = __getData(params);

        data = __addMethodToData(data, params);

        return window.__ibCallAjax(url, type, data, params);
    }

    function __manageSuccessMessages(response)
    {
        if(typeof response.message !== 'undefined')
            window.addSuccessNotification(response.message);

        else
            window.addSuccessNotification('elemento aggiornato');
    }

    window.__displayResponseErrors = function(jsonResponse)
    {
        // jsonResponse = response.responseJSON;

        if(typeof jsonResponse.exception !== 'undefined')
            alert(jsonResponse.message);

        if(typeof jsonResponse.errors !== 'undefined')
            for (var field in jsonResponse.errors)
            {
                let errorMessage = field;

                for (var error in jsonResponse.errors[field])
                    errorMessage += ' ' + jsonResponse.errors[field][error];

                window.addDangerNotification(errorMessage);
            }
    }

    window.__getIdColumnIndex = function(target, table)
    {
        //never used as override by the moment
        if($(target).data('idcolumn'))
            return $(target).data('idcolumn');

        return table.init().rowId;
    }
});
    
</script>