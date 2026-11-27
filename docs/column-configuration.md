# Configurazione della tabella

## Colori di sfondo nel FieldsGroupParametersFile

Ogni campo può definire il colore del titolo con `thBackgroundColor` e quello
delle celle dati con `tdBackgroundColor`. Per colorare un'intestazione di gruppo,
aggiungere `backgroundColor` al `mainHeader` del campo che apre il gruppo.

```php
'km_letter_status' => [
    'type' => 'flat',
    'translatedName' => 'Stato',
    'thBackgroundColor' => '#dbeafe',
    'tdBackgroundColor' => '#eff6ff',
    'mainHeader' => [
        'label' => 'Lettera incarico veicolo',
        'colspan' => 3,
        'backgroundColor' => 'var(--colore-gruppo)',
    ],
],
```

Le tre opzioni sono indipendenti e funzionano anche con i campi `ibDtV2`.
Il colore del gruppo copre il suo `colspan`. Filtri e riepiloghi mantengono
lo stile del tema. Puoi usare colori esadecimali, nomi CSS, `rgb()`, `rgba()`,
`hsl()`, `hsla()` e variabili definite nel tema con `var(--nome)`.
Un colore personale salvato per il `td` ha precedenza sul colore del campo.

## Personalizzazione dalla tabella

Con `canEditColumnStyles()` attivo, il pulsante **Modifica tabella** abilita gli
attivatori contestuali. **Fine modifica** ripristina l'interazione ordinaria.
La modalità è locale alla tabella e non viene salvata come preferenza.

L'app deve abilitare `datatables.columnSettings` (il default del pacchetto è
`false`), per esempio aggiungendo a `config/datatables.php`
`'columnSettings' => env('DATATABLES_COLUMN_SETTINGS', true)`. Il flag controlla
la generazione del pulsante e dei metadati del pannello nei template Blade:
la sola compilazione JavaScript non lo abilita. Un valore esplicito di
`$table->canEditColumnStyles` prevale sulla configurazione globale.

- L'ingranaggio dell'intestazione apre l'editor esistente sulla colonna.
- Nelle celle si può scegliere un elemento con clic, tastiera o con il selettore
  contestuale che permette di risalire a collegamento, lista e intera cella.
  I clic non eseguono link, toggle o editor mentre si configura il campo.
- Le personalizzazioni valgono per tutte le righe della colonna. L'editor rimane
  unico, creato su richiesta; la ricerca degli elementi continua per blocchi
  sulla pagina corrente. Salvataggio e annullamento restano quelli dello stile
  della singola colonna.

## Booleani

Booleani e toggle espongono i target `boolean-true`, `boolean-false` e
`boolean-null`, anche quando uno stato non compare nella pagina. I nomi delle
icone Font Awesome vengono conservati nella proprietà validata `__icon` delle
impostazioni del target. Famiglia e icone iniziali vengono dal campo backend.
La scelta di uno stato cambia solo l'anteprima, mai il valore del dato. Le icone
disponibili sono quelle installate dall'applicazione ospite; il controllo
propone alcuni nomi e consente di specificarne altri.

## Larghezze

La larghezza effettiva è quella di `selectors.td.width`, altrimenti
`getUnstyledWidth()`. `width`, `min-width` e `max-width` backend continuano a usare
`!important`, attraverso `--ib-column-width`. Il layout delle tabelle con
configurazione attiva usa colonne di dimensione determinata e larghezza totale
corrispondente: il layout automatico non redistribuisce lo spazio aumentando
una colonna che l'utente ha ristretto.

Il calcolo segue visibilità e riordino, aggiorna colgroup e intestazioni clonate,
e converte px/em/rem/% nel contesto della tabella. Le colonne `auto` usano la
misura disponibile. Le sole dimensioni inline che eccedono la larghezza scelta
vengono limitate conservandone valore e priorità per il ripristino. Gli stili
interni estranei alla dimensione restano invariati.

Subito sotto la larghezza, “Overflow del testo” permette di scegliere:

- **Predefinito**: mantiene il comportamento originale del campo.
- **Visibile oltre la cella**: una sola riga, anche fuori dalla colonna; può sovrapporsi al contenuto successivo.
- **A capo**: mostra tutto il testo su più righe, incluse email senza spazi.
- **Taglia senza puntini**: nasconde la parte che supera il bordo.
- **Tronca con puntini (…)**: accorcia il testo su una sola riga.
- **Puntini + tooltip**: aggiunge il testo completo al passaggio del mouse.

La scelta si applica in anteprima all’intera cella o al singolo collegamento,
prevale sul troncamento predefinito dei campi email e viene salvata nello stile.
L’indirizzo completo e il collegamento `mailto:` non vengono modificati.

## Ordine personale e main header

Il riordino richiede ColReorder 2 (verificato con 2.1.2 e DataTables 2.3.8).
Le maniglie nelle intestazioni e nell'elenco laterale consentono trascinamento
e spostamento tramite frecce della tastiera. Fuori dalla modalità modifica sono
disabilitate. La colonna di selezione righe resta fissa.

**Scelta sui gruppi:** un campo può muoversi solo dentro il proprio gruppo
contiguo del main header. Non si attraversano i confini dei colspan. Questo
mantiene titoli e appartenenza corretti, anche nascondendo colonne. I gruppi
non vengono spezzati né spostati come blocchi in questa implementazione.

L'ordine è salvato per nome di campo in `DatatableUserData.uiSettings.columnOrder`,
indipendentemente da `stateSave`. I campi rimossi vengono ignorati; i nuovi campi
restano nel loro gruppo. **Ripristina ordine originale** usa l'ordine backend
corrente. Le scritture sono serializzate e un errore ripristina l'ultimo ordine
confermato. Il riordino riallinea gli indici usati da filtri, riepiloghi,
gruppi di visibilità ed editor, mantenendo le sorgenti dei dati originali.

Non si può iniziare una configurazione o riordinare mentre ci sono modifiche
ai dati o salvataggi di celle ancora da completare.

## Indici originali e indici correnti

Gli header dei campi, i filtri in testa e in fondo e i riepiloghi espongono:

| Attributo | Significato |
| --- | --- |
| `data-original-column-index` | Indice originale emesso dal backend, immutabile durante riordino, visibilità e ripristino. |
| `data-column` | Indice corrente DataTables, incluse le colonne nascoste. |
| `data-field-name` | Identità stabile del campo. |

Le copie per scrolling/FixedHeader conservano gli stessi metadati. I titoli
raggruppati del main header non ricevono un indice originale di campo.

L'API `table.columnIndexFromOriginal(originalIndex)` risolve il riferimento al
momento dell'uso. È registrata da `datatables.vendor.columnIndexes.js`, incluso
nell'entrypoint comune, ed è disponibile anche senza ColReorder o configuratore.
Accetta interi non negativi e stringhe di sole cifre (spazi esterni ammessi).
Restituisce un intero oppure `null` per input non valido, campo non trovato,
metadati mancanti/ambigui o un'API che non identifica esattamente una tabella.
Non restituisce l'indice tra le sole colonne visibili: una colonna nascosta
continua ad avere il suo indice corrente DataTables.

```js
// Conservare questo riferimento, non una copia di data-column.
const original = header.dataset.originalColumnIndex;

function leggiCella(rowIndex) {
    const current = table.columnIndexFromOriginal(original);
    if (current === null) return;
    return table.cell(rowIndex, current).data();
}
```

Nel JSON standard del pacchetto, `table.row(rowIndex).data()[Number(original)]`
continua invece a riferirsi alla posizione originale del valore: ColReorder 2
non riordina l'array di dati. Per datasource applicativi personalizzati usare
la sorgente effettiva del campo, senza presumere che sia un array posizionale.
Non passare mai `null` a `table.column()`/`table.cell()` come ripiego.

Questa API non migra automaticamente gli script che hanno già catturato un
`data-column`. I consumatori applicativi devono conservare l'originale e fare
la conversione prima di ogni accesso alle API indicizzate, distinguendolo dagli
accessi al JSON grezzo. L'indice originale vale per lo schema di quella tabella;
per riferimenti persistenti tra cambiamenti di schema usare `data-field-name`.

## Asset dell'applicazione ospite

In TimeManagement `resources/js/app.js` importa direttamente l'entrypoint del
pacchetto tramite l'alias `@ilbronza`; `vendor/ilbronza/datatables` è un link a
questo checkout. Anche `resources/less/app.less` importa il LESS del pacchetto.
Per rendere queste modifiche disponibili nell'app occorre eseguire `npm run build`
da `/Volumes/Lavoro/Progetti/ilbronza/TimeManagement`: Laravel Mix rigenera
`public/js/app.js` e `public/css/app.css`. Non serve pubblicare gli asset con
`vendor:publish` in questa installazione. La verifica del pacchetto usa un bundle
temporaneo e non aggiorna gli asset pubblici dell'applicazione.

Idealpack importa anch'esso i sorgenti dal pacchetto collegato in `vendor`, ma
usa `npm run development` per il build locale (`npm run production` per quello
ottimizzato). La configurazione dell'app abilita `columnSettings`; aggiornare
NPM senza abilitare questo flag non rende visibile il pulsante. Gli stili del
pannello sovrascrivono esplicitamente titolo, bordi e placeholder inversi dei
temi UIkit che vengono caricati dopo `app.css`.
