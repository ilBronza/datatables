jQuery(document).ready(function ($)
{
    (function ()
    {
        // se hai un footer/sticky bar in basso, indica la sua altezza qui
        var BOTTOM_OFFSET = 0; // es. 48 o 64; considera anche env(safe-area-inset-bottom) sui device mobili

        function fitWindowWrapper()
        {
            var els = document.querySelectorAll('.dataTables_scrollBody, .dt-scroll-body');

            els.forEach(function(el) {
                var rect = el.getBoundingClientRect();
                var safeInset = (typeof CSS !== 'undefined' && CSS.supports('padding-bottom: env(safe-area-inset-bottom)'))
                    ? (parseInt(getComputedStyle(document.documentElement).getPropertyValue('--safe-area-bottom')) || 0)
                    : 0;

                var available = window.innerHeight - rect.top - BOTTOM_OFFSET - safeInset;
                if (available < 500) available = 500;

                el.style.maxHeight = (available - 100) + 'px';
            });




            // var el = document.querySelector('.dataTables_scrollBody');
            //
            // if (!el)
            //     el = document.querySelector('.dt-scroll-body');
            //
            // if (!el)
            //     return;
            //
            // // posizione dell’elemento rispetto al viewport ad ogni ricalcolo
            // var rect = el.getBoundingClientRect();
            // var safeInset = (typeof CSS !== 'undefined' && CSS.supports('padding-bottom: env(safe-area-inset-bottom)'))
            //     ? (parseInt(getComputedStyle(document.documentElement).getPropertyValue('--safe-area-bottom')) || 0)
            //     : 0;
            //
            // var available = window.innerHeight - rect.top - BOTTOM_OFFSET - safeInset;
            // // fallback di sicurezza
            // if (available < 100) available = 100;
            //
            // el.style.maxHeight = (available - 100) + 'px';
        }

        // primo calcolo quando il DOM è pronto
        if (document.readyState === 'loading')
        {
            document.addEventListener('DOMContentLoaded', fitWindowWrapper, {once: true});
        } else
        {
            fitWindowWrapper();
        }

        // ricalcola su resize/zoom o cambi layout
        window.addEventListener('resize', fitWindowWrapper);

        $(document).on(
            'init.dt draw.dt responsive-resize.dt column-visibility.dt',
            'table.dataTable',
            function () {
                fitWindowWrapper();
                setTimeout(fitWindowWrapper, 0); // subito dopo il reflow/layout
            }
        );

        // se esiste la DataTable, ricalcola anche dopo draw/colonne responsive
        // function hookDataTables()
        // {
        //     var dt = window.table{{ $table->getId() }};
        //
        //     if (!dt || !dt.table) return;
        //
        //     $('#{{ $table->getId() }}')
        //         .on('init.dt draw.dt xhr.dt processing.dt page.dt order.dt search.dt responsive-resize.dt column-visibility.dt column-reorder.dt', function ()
        //         {
        //             console.log('sto facendo il; ricalcolo di fitWindowWrapper');
        //             // subito
        //             fitWindowWrapper();
        //
        //             // e subito dopo l’aggiornamento del layout
        //             setTimeout(fitWindowWrapper, 0);
        //         });
        //
        //     return true;
        // }
        //
        // var iv = setInterval(function ()
        // {
        //     if (hookDataTables()) clearInterval(iv);
        // }, 100);


        // opzionale: osserva cambi dimensione contenuto interno (aperture/chiusure pannelli, filtri, ecc.)
        // try
        // {
        //     var target = document.querySelector('.dataTables_scrollBody');
        //     if (target && 'ResizeObserver' in window)
        //     {
        //         var ro = new ResizeObserver(function ()
        //         {
        //             fitWindowWrapper();
        //         });
        //         ro.observe(target);
        //     }
        // } catch (e)
        // {
        // }
    })();

});