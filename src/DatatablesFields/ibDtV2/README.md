# ibDtV2

Famiglia autonoma di campi DataTables. La base `ibDtV2\DatatableField` non
estende il `DatatableField` legacy e non importa i suoi trait. Parametri e
comportamenti sono definiti nei trait V2 e composti dai tipi specifici.

La tabella usa `Contracts\DatatableFieldContract` per accettare entrambe le
famiglie. `DatatablesFields\FieldFactory` risolve i tipi senza dipendere dalla
base legacy; i vecchi metodi statici di creazione restano disponibili.

Il namespace riservato è `IlBronza\Datatables\DatatablesFields\ibDtV2`.
Il resolver conserva esattamente le maiuscole e minuscole di `ibDtV2`.

Sono disponibili `ibDtV2.editor.text`, `ibDtV2.editor.textarea` e
`ibDtV2.editor.numeric`:

```php
'notes' => [
    'type' => 'ibDtV2.editor.text',
    'disabledIf' => 'isLocked', // facoltativo: metodo pubblico sul model
    'inputAttributes' => [
        'placeholder' => 'Inserisci una nota',
        'maxlength' => 200,
    ],
],
```

Questo tipo risolve la classe
`IlBronza\Datatables\DatatablesFields\ibDtV2\Editor\DatatableFieldText`.
Per usare il nuovo campo nell'applicazione pubblicare gli asset aggiornati
(`datatables.assets`) e ricompilare il bundle che importa `ilBronza.datatables.js`.
L'entry point include `datatables.vendor.editorHelpers.js`,
`datatables.vendor.numericHelpers.js`, `datatables.vendor.editors.js`,
`datatables.vendor.numericEditor.js` e `datatables.vendor.editorHistory.js`.

## Numeric

```php
'amount' => [
    'type' => 'ibDtV2.editor.numeric',
    'decimals' => 2,
    'fixedDecimals' => true,
    'maxDecimals' => 6,
    'decimalSeparator' => ',',
    'thousandsSeparator' => '.',
    'min' => '0',
    'max' => null,
    'step' => null,
    'currency' => 'EUR',
],
```

`DatatableFieldNumeric` estende il `DatatableFieldText` V2 e riusa il renderer
comune tramite `getInputDisplayValueExpression()`, binding, attributi, permessi,
disabilitazione, route, inline, bulk e cronologia. `NumericOptionsTrait` contiene
le sole opzioni numeriche. Non usa le classi o i parser degli editor legacy.

| Parametro | Default e significato |
| --- | --- |
| `decimals` | `null`: precisione originale. Un intero 0–100 arrotonda solo la visualizzazione, a metà lontano dallo zero. |
| `fixedDecimals` | `true`: conserva gli zeri finali; con `decimals` impostato completa le cifre mancanti. `false` elimina gli zeri finali dal display. |
| `maxDecimals` | `null`: nessun limite aggiuntivo. Un intero 0–100 limita i decimali significativi inseribili; gli zeri finali aggiuntivi sono ammessi e conservati nel dato. `0` ammette solo valori interi. |
| `decimalSeparator` | `,`; ammessi `,` e `.`. |
| `thousandsSeparator` | `.`; ammessi anche `,`, spazio, NBSP, spazio stretto e apostrofo. `''` disattiva il raggruppamento. Deve differire dal separatore decimale. |
| `min`, `max` | `null`: nessun limite. Estremi inclusivi, preferibilmente stringhe decimali canoniche. |
| `step` | `null`: nessun vincolo di passo. Un decimale positivo richiede multipli esatti a partire da `min`, oppure da zero quando `min` non è impostato. |
| `currency` | `null`, oppure codice maiuscolo di tre lettere, es. `EUR`. Mostrato come suffisso testuale, senza conversioni o decimali impliciti; `suffix` esplicito ha precedenza. |

L'input resta `type="text"`, con `inputmode="decimal"` (`numeric` per soli
interi), per consentire i separatori configurati e numeri oltre il limite degli
interi JavaScript. `min`, `max` e `step` vanno dichiarati come parametri del campo.
La larghezza predefinita è `125px`, l'allineamento è a destra.

Il valore del model viene serializzato come stringa decimale con il punto,
senza raggruppamento o valuta; `null` resta `null` e zero resta visibile. Per
conservare tutta la precisione della sorgente, il model e le risposte di
salvataggio devono fornire stringhe decimali (per esempio tramite un cast
Eloquent `decimal`). La precisione già persa da un float del backend o da un
numero JSON non è ricostruibile. Gli interi JSON oltre `MAX_SAFE_INTEGER`
vengono rifiutati esplicitamente nel browser.

Per esempio `12.345678` con `decimals => 2` si vede come `12,35`, ma si salva
come `"12.345678"`. In focus il controllo mostra il valore completo senza
raggruppamento; uscendo torna formattato. Focus/blur senza modifiche preservano
il dato completo, anche se la precisione visualizzata è inferiore. Modificare
solo separatore o zeri finali non rende il campo dirty. Un evento `input` che
scrive intenzionalmente il valore arrotondato viene invece trattato come modifica.

Durante la modifica un singolo punto o virgola è accettato come separatore
decimale. Gli incolla con raggruppamento devono rispettare i separatori
configurati e gruppi completi di tre cifre (`1.234,56` nel formato italiano).
Un singolo `1.234` digitato in modifica significa `1.234`, mentre un display
raggruppato non modificato conserva il suo valore originale. Testo non numerico,
valute nell'input e notazione scientifica digitata vengono rifiutati.

Gli helper `editors.helpers.numbers` usano stringhe e `BigInt` per arrotondamento,
confronti e verifica del passo. Le richieste singole, inline edit/create e bulk
inviano stringhe canoniche e controllano limiti e precisione prima della scrittura;
un errore usa la validazione nativa del controllo e mantiene il draft. I vincoli
del browser non sostituiscono le regole applicative del backend Laravel.

Filtro ed export ricevono il dato originale, senza formattazione. Il tipo
DataTables `ib-decimal` ordina esattamente le stringhe decimali, mantenendo i
null alla fine in entrambe le direzioni. La cronologia distingue zero da null
e riconosce scale numeriche equivalenti, come `1.0` e `1.000`.

`NumericEditorContext` è registrato nella factory comune e viene creato solo
per i controlli numerici nuovi. Gli handler legacy restano attivi per i vecchi
numeric/price. Per frammenti inline inseriti manualmente usare
`IlBronza.datatables.editors.initialize(root)` per applicare il display iniziale;
i flussi inline del pacchetto lo fanno automaticamente. Le API `save(value)` e
`setValue(value)` accettano valori canonici; `save()` legge il draft localizzato.

## Textarea

```php
'notes' => [
    'type' => 'ibDtV2.editor.textarea',
    'inputAttributes' => [
        'rows' => 4,
        'maxlength' => 2000,
        'placeholder' => 'Inserisci le note',
    ],
],
```

`Editor\DatatableFieldTextarea` estende `Editor\DatatableFieldText` e usa lo
stesso `InputControlTrait`, binding, controller e cronologia. Il trait comune
genera `<input>` oppure `<textarea>` sia nel renderer sia nei form inline.
Il testo multilinea è contenuto nel tag, con escaping; `type` e `value` non
sono attributi del textarea. Le righe vuote iniziali e finali sono conservate.
Come nei textarea HTML nativi, CR e CRLF vengono normalizzati a LF nel controllo;
filtro, ordinamento ed export continuano a ricevere il dato originale.

Il default è `rows="3"`, la classe UIkit è `uk-textarea` e la larghezza di
default è `250px`, configurabile con `width` o
`datatables.datatableFieldWidths.ibDtV2.editor.datatableFieldTextarea`.
`inputAttributes` consente anche `cols`, `wrap`, `readonly`, ecc.

Invio inserisce una nuova riga e le frecce muovono il cursore dentro il testo.
Il default `saveTrigger` segue la configurazione degli editor per `blur` e
`tab`; il default globale `enter` diventa `blur`. Sono ammessi soltanto `blur`
e `tab`: un `saveTrigger => 'enter'` esplicito produce un errore. `saveButton`
mantiene il funzionamento comune. Nei form inline edit/create salva il pulsante
della riga. Escape ripristina il valore precedente nei controlli ordinari.

La derivazione inline mantiene automaticamente `ibDtV2.editor.textarea`. La
modifica massiva usa un textarea e conserva gli a capo, compresi quelli finali,
senza equiparare valori che differiscono solo per spazi o righe vuote.

## Binding e parametri

| Parametro | Significato |
| --- | --- |
| `editorProperty` | Proprietà da leggere, anche con percorso puntato. Default: nome del campo. |
| `parameter` | Nome inviato al server in `field`. Default: nome del campo, indipendente da `editorProperty`. |
| `editorValueFunction` | Metodo per leggere un valore calcolato sul model. |
| `inlineEditProperty` | Percorso di lettura alternativo solo per i form inline. |
| `forceValue` | Valore forzato, applicato quando diverso da `null`; zero e stringa vuota sono validi. |
| `editorTarget` | `row` oppure `element`. Default: `row` per campi diretti, `element` per figli o campi con `solveElement`. |
| `modelClass`, `placeholderElement` | Model da usare per risolvere le route, quando diverso da quello della tabella. |
| `customUpdateRouteName` | Route di aggiornamento alternativa, come negli editor attuali. |
| `updateUrlMethod` | Facoltativo: metodo pubblico sul record che restituisce un URL di aggiornamento specifico della cella, oppure `null`/stringa vuota per usare il template della colonna. |
| `editorRoles` | Ruoli ammessi alla modifica. Senza permessi la tabella mostra testo escaped. |
| `disabled` | Disabilitazione fissa, default `false`. |
| `disabledIf` | Nome di un metodo pubblico del model; viene combinato con `disabled` tramite OR. |
| `saveTrigger` | `enter`, `blur` o `tab` per il testo; `blur` o `tab` per textarea. |
| `saveButton`, `nullable`, `refreshRow`, `reloadTable`, `spin` | Impostazioni implementate dai trait V2; i default di salvataggio usano la configurazione editor della tabella. |

La lettura segue `forceValue`, poi `inlineEditProperty` nei form inline, poi
`editorValueFunction`, infine `editorProperty` o nome del campo. Il parametro
inviato rimane sempre `parameter` o nome del campo.

I `th` della colonna (titolo e filtro) contengono i metadati condivisi, senza
ripeterli sugli input: `data-save-trigger` per `saveTrigger`, `data-field` per il
parametro inviato al server, `data-ib-dt-value-type` per il confronto dei valori
(`text` mantiene distinti `001` e `1`) e `data-ib-dt-cell-format` per aggiornare
correttamente un valore scalare (`scalar`) o un oggetto con chiave `value`.
Il JavaScript legge questi dati con
`IlBronza.datatables.editors.forInput(input).data(name)`, dove
`name` è il nome senza `data-`, e risale all'header
tramite l'indice DataTables, anche con colonne nascoste/riordinate e intestazioni
clonate per lo scrolling. I frammenti inline senza header conservano i metadati
nel contenitore `data-ib-dt-inline-control`. I fallback alla configurazione del
campo/tabella restano disponibili; gli editor legacy mantengono la loro lettura.

Con `disabledIf` o `updateUrlMethod` viene richiesto il refresh della riga dopo il
salvataggio, così il server ricalcola stato e route. `refreshRow => false` disattiva
esplicitamente questa richiesta; in quel caso i metadati restano quelli
dell'ultima risposta dati.
`reloadTable` mantiene la precedenza sul refresh della riga.

## JSON della cella

Se l'editor modifica il model della riga e non ci sono metadati dinamici, la
cella contiene direttamente il valore:

```json
"Una nota"
```

Con `disabledIf`, tutte le righe della colonna usano lo stesso oggetto:

```json
{"value": "Una nota", "disabled": false}
```

Con `editorTarget => 'element'` compare anche `targetId`, combinabile con
`disabled`:

```json
{"value": "Una nota", "targetId": 42}
```

La scelta dipende dalla configurazione della colonna, mai dal confronto fra gli
ID delle singole righe. Per il target `row` l'ID viene letto dalla colonna `rowId`
della tabella. Per il target `element` viene letto dal model passato al campo.
Un target mancante produce un controllo disabilitato.

Gli input V2 non contengono `data-url`. Il template di aggiornamento e il relativo
placeholder sono definiti una volta nella configurazione `ibDtEditor` della
colonna. Il salvataggio singolo e bulk risolvono l'URL al momento della richiesta,
usando il getter `rowId` di DataTables; per `editorTarget => 'element'` usano
invece `data-ib-dt-target-id`. L'ID viene codificato come segmento URL. Colonne
nascoste o riordinate non cambiano il campo o il model destinatario. Se mancano
configurazione o ID, la richiesta non viene inviata.

Per una route che dipende dal record concreto, dichiarare esplicitamente
`updateUrlMethod` (default `null`):

```php
'notes' => [
    'type' => 'ibDtV2.editor.text',
    'updateUrlMethod' => 'getNotesUpdateUrl',
],
```

Il metodo pubblico `getNotesUpdateUrl()` viene chiamato su ogni model risolto dal
campo e deve restituire un URL completo o relativo come stringa, oppure `null`.
La cella usa sempre un oggetto, anche quando il metodo restituisce `null` o una
stringa vuota; le stringhe vuote vengono normalizzate a `null`:

```json
{"value": "Una nota", "updateUrl": "/records/42/notes"}
```

`updateUrl` resta nel JSON e non viene copiato nell'input. Al salvataggio singolo
e bulk ha precedenza sul template della colonna; `null` o stringa vuota usano il
template e l'ID normalmente. Il JSON può includere anche `disabled` e `targetId`.
Il valore dell'editor e i valori di filtro, ordinamento ed export rimangono
separati dalla route. Un metodo mancante/non pubblico o un risultato diverso
da stringa/null genera un errore esplicito. Senza `updateUrlMethod` il formato
della cella e il comportamento delle route rimangono quelli ordinari.

I frammenti inline, generati separatamente e solo all'apertura del form, conservano
template e ID nel contenitore `data-ib-dt-inline-control`, tramite
`data-ib-dt-editor-route`. Anche qui l'input non contiene URL. Il pulsante batch
risolve la route dal primo campo al salvataggio; inline create conserva la propria
route di creazione. Questo mantiene le route personalizzate e l'uso senza tabella.
Con `updateUrlMethod` i metadati del contenitore inline includono anche l'URL
calcolato sul record concreto; inline create usa sempre la route di creazione.

`null`, stringa vuota e zero rimangono distinti nei dati. Nell'input `null` viene
mostrato come vuoto, zero come `0`. Il confronto del testo è letterale:
`001` e `1`, oppure `1.0` e `1`, sono valori diversi. Il renderer non modifica
il JSON ricevuto. Filtro, ordinamento, export e rilevamento del tipo ricevono il
valore senza HTML, prefissi o suffissi.

## Attributi e rendering

- `htmlClasses` e `data` continuano a riferirsi al controllo.
- `inputAttributes` aggiunge attributi HTML all'input.
- `containerAttributes` configura il contenitore, creato solo quando serve.
- `cellAttributes` configura il `td` tramite `createdCell`; le classi si
  aggiungono a quelle della tabella. Rimane disponibile `tDHtmlClasses`.
- `prefix` e `suffix` sono testo e vengono escaped, come valori e attributi.

`type`, `value`, `disabled` e gli attributi di binding/salvataggio sono gestiti
dal campo. Le omonime chiavi personalizzate non li sovrascrivono. Non inserire
frammenti JavaScript nei valori degli attributi: sono trattati come dati.

PHP compone il renderer della colonna usando soltanto i contributi necessari.
Gli attributi statici vengono serializzati durante la compilazione; quelli
dinamici usano espressioni dichiarate dai trait. Il JavaScript condiviso fornisce
escaping e aggiornamento dei dati dopo il salvataggio, senza interpretare una
configurazione generica durante il rendering.

## Organizzazione

| Trait | Responsabilità |
| --- | --- |
| `Traits/EditorBindingTrait` | Lettura del valore, formato della cella, target e integrazione editor V2. |
| `Traits/NumericOptionsTrait` | Configurazione di precisione, separatori, limiti, passo e valuta del numeric. |
| `Traits/HtmlAttributesTrait` | Attributi statici/dinamici, escaping e contenitori. |
| `Traits/InputControlTrait` | Input e textarea in tabella e nei form inline, stesso contratto degli attributi. |
| `Traits/DisabledStateTrait` | Stato disabilitato fisso o calcolato sul model. |
| `Traits/RendererTrait` | Composizione dei renderer per display, filtro, sort, export e tipo. |
| `Traits/EditorRoutesTrait` | URL di aggiornamento e binding dei form inline. |
| `Traits/EditorPermissionsTrait` | Ruoli ammessi alla modifica. |
| `Traits/EditorSaveSettingsTrait` | Refresh, reload, pulsante di salvataggio, nullable e spinner. |
| `Traits/ModelBindingTrait` | Model del campo, placeholder e risoluzione del target. |
| `Traits/FieldIdentityTrait` | Nome, indice e collegamento alla tabella/gruppo. |
| `Traits/FieldPermissionsTrait` | Visibilità del campo in base ai ruoli. |
| `Traits/ColumnOptionsTrait` | Larghezza, ordinamento, visibilità, CSS e gruppi logici. |
| `Traits/HeaderTrait` | Etichette, tooltip e filtro testuale. |
| `Traits/TableIntegrationTrait` | Default per le funzionalità opzionali richieste dalle viste della tabella. |

Tutti questi trait sono nel namespace V2. La gerarchia è
`ibDtV2\Editor\DatatableFieldText` → `ibDtV2\Editor\DatatableFieldEditor` →
`ibDtV2\DatatableField`, che implementa il contratto senza estendere altre classi.
L'editor legacy conserva la propria implementazione.
`ibDtV2\Editor\DatatableFieldTextarea` estende il `DatatableFieldText` V2.
Anche `ibDtV2\Editor\DatatableFieldNumeric` estende il `DatatableFieldText` V2.

V2 accetta soltanto parametri dichiarati dai propri trait. Un parametro
legacy non implementato, per esempio `fetcher`, genera un errore esplicito:
non abilita comportamenti ereditati e non viene ignorato. In questa prima
versione non sono implementati fetcher, field operations, riepiloghi e filtri
per intervallo. Il filtro disponibile è quello testuale, disattivabile con
`filterable => false`.

La larghezza usa `width` oppure
`datatables.datatableFieldWidths.ibDtV2.editor.datatableFieldText`, default
`125px`. Le preferenze di larghezza salvate per la cella hanno precedenza.
Per un campo figlio dichiarare `modelClass` o `placeholderElement`; V2 non
usa il meccanismo di risoluzione dei figli legacy.

Il corpo del renderer assegna `item` senza `return`; il compositore della
colonna possiede il ritorno finale. Questo prepara il riuso nei contenitori.
La migrazione completa degli iteratori, inclusi ricerca e ordinamento aggregati,
rimane separata da questo primo campo.

Il campo partecipa a inline edit/create. La derivazione automatica inline
conserva binding, attributi e disabilitazione della sorgente V2. Il bulk è
disponibile per text, textarea e numeric sul model della riga, se modificabili e privi di
disabilitazione fissa/condizionale. I target diversi dalla riga non vengono
derivati automaticamente nei form che salvano la riga in blocco.

## Oggetti e helper JavaScript

Il punto di ingresso è `window.IlBronza.datatables.editors`. I nomi dei metodi
non dipendono dalla versione del campo. `ibDtV2.editor.text` e la classe CSS
`ib-dt-editor-v2` restano identificatori di migrazione PHP/DOM.

- `helpers` contiene escaping, copia e confronto dei valori, aggiornamento
  immutabile delle celle, composizione delle route e lookup DataTables.
- `forTable(table)` restituisce sempre lo stesso `TableEditorController` per
  l'istanza DataTables. I controller sono in una `WeakMap` indicizzata dalle
  impostazioni dell'istanza: un draw o un reload AJAX li conserva; una nuova
  DataTable crea un nuovo controller. Il controller gestisce salvataggio,
  lettura delle righe, localizzazione delle celle e la propria `history`.
- `forInput(input)` e `controller.editorFor(input)` creano un contesto temporaneo.
  `EditorContext` legge header, wrapper inline e route della colonna;
  `LegacyEditorContext` mantiene gli attributi e la cache jQuery degli editor
  precedenti. I contesti espongono `data()`, `getValue()`, `resolveUrl()` e `save()`.
  Non conservarli tra draw: gli input vengono ricreati da DataTables.
- `History` conserva privatamente le pile del singolo controller. La facade
  `editors.history` instrada le richieste dei moduli AJAX e bulk al proprietario.

```js
const editors = IlBronza.datatables.editors;
const controller = editors.forTable(table);
const editor = controller.editorFor(input);

editor.data('field');
editor.resolveUrl();
editor.save(editor.getValue()); // stesso trasporto AJAX degli editor esistenti
await controller.history.undo();
```

I renderer restano compilati da PHP e usano `editors.helpers.escapeHtml()`.
Gli eventi condivisi continuano a gestire conferme, draft e feedback; i
salvataggi V2 passano dal contesto al controller prima del trasporto comune.
I moduli legacy possono ancora essere caricati senza i nuovi moduli.
`ibDtGetEditorData` e `ibDtEscapeHtml` rimangono bridge di compatibilità verso
l'API a oggetti; non vengono esposte funzioni globali con nomi contenenti `V2`.

## Undo / redo in memoria

Gli editor V2 hanno una cronologia per istanza DataTables, mantenuta in una
`WeakMap` privata in `datatables.vendor.editorHistory.js`, posseduta dalla
cronologia del controller della tabella. Le pile undo/redo
contengono al massimo 100 operazioni con ID della riga e del destinatario, nome
stabile della colonna, parametro, valore precedente e valore confermato. Non
vengono conservati nodi DOM né scritti dati in database, cookie, localStorage o
sessionStorage. Un refresh della pagina o la ricreazione della DataTable azzera
la cronologia; draw, filtri, riordino e reload AJAX la mantengono.

Le operazioni vengono registrate solo dopo una risposta HTTP riuscita che non
dichiari `success: false`. Si usa il valore restituito dal server; per batch e
risposte prive di valori, una lettura della riga recupera i valori confermati.
I salvataggi bulk sono raggruppati e gli inline batch vengono annullati con una
richiesta batch. Nei gruppi misti la cronologia comprende solo i campi V2.
Un nuovo salvataggio riuscito svuota redo; un salvataggio fallito non crea voci.

Con il focus nella tabella, `Ctrl/Cmd+Z` annulla e `Ctrl/Cmd+Shift+Z` ripete
(`Ctrl+Y` ripete anch'esso). Dentro un input con modifiche non salvate rimane
l'undo nativo del browser. L'undo applicativo attende la conclusione delle
richieste in corso e richiede di salvare/annullare gli altri draft della tabella.
Durante la verifica e il ripristino i controlli V2 vengono sospesi temporaneamente.

Prima di inviare un valore precedente viene riletta ogni riga interessata, usando
lo stesso endpoint AJAX del refresh. Se valore, destinatario o disponibilità del
campo sono cambiati, l'operazione si interrompe senza sovrascrivere il nuovo stato.
Questa lettura è una verifica preventiva, non un confronto atomico sul database:
un aggiornamento simultaneo tra verifica e scrittura richiede un protocollo di
concorrenza lato backend. I salvataggi inversi usano le route attuali, comprese
quelle di `updateUrlMethod`, e conservano i dati aggiuntivi della richiesta.

Se un gruppo su più righe riesce solo parzialmente, le due pile vengono divise
tra modifiche effettivamente ripristinate e modifiche ancora da ripristinare.
Gli inline batch conservano l'atomicità fornita dal loro endpoint. I campi con
`forceValue` o `editorValueFunction` non entrano nella cronologia, perché il valore
mostrato non consente di ricostruire con certezza quello originario da salvare.

API disponibile per pulsanti o strumenti applicativi:

```js
const controller = IlBronza.datatables.editors.forTable(table);
await controller.history.undo();
await controller.history.redo();
controller.history.state(); // undo, redo, pending, busy, lastError
controller.history.clear();
```

`forTable()` accetta un'API DataTables, un elemento tabella o un selettore.
La facade `IlBronza.datatables.editors.history` espone gli stessi metodi con
la tabella come argomento per i moduli AJAX e bulk condivisi. L'evento
`ib:dt-editor-history` sulla tabella comunica i contatori aggiornati, senza esporre
i valori della cronologia.

## Verifiche

`EditorV2TextTest.php` verifica l'assenza di antenati e trait legacy, l'uso
senza tabella, dati, binding, permessi, attributi, forme inline, bulk e una
tabella mista con intestazioni, filtri e CSS Blade. `editorV2.test.js` genera i renderer
eseguendo il PHP reale e li esegue con Node, verificando anche l'aggiornamento
AJAX dei dati. Non usa copie statiche dei renderer.
`editorV2.dom.test.js` usa jQuery, DataTables e ColReorder reali in jsdom per
verificare gli URL effettivi delle richieste singole, bulk e inline, compresi ID
zero, ID mancanti, escaping, colonne nascoste e riordinate e convivenza con gli
editor legacy. Le dipendenze DOM sono descritte in `tests/README.md`.

```sh
node --test tests/editorV2.test.js tests/editorNumeric.test.js tests/inlineEditSave.test.js
NODE_PATH=/tmp/ib-table-config-tests/node_modules node --test tests/editorV2.dom.test.js
```

Il test JavaScript usa `php` e `vendor/autoload.php`; se presente, può usare il
bootstrap condiviso `../.ibtest/bootstrap_datatables.php`. Si possono indicare
un eseguibile PHP e un bootstrap diversi tramite `IB_DT_PHP` e `IB_DT_BOOTSTRAP`.

Durante la migrazione i tipi attuali e quelli nuovi convivono. Al completamento,
i nomi canonici potranno puntare alle nuove implementazioni; solo allora sarà
possibile rimuovere `ibDtV2.` dai valori `type`, dopo aver verificato la
compatibilità dei parametri.
