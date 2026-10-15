# ibDtV2

Famiglia autonoma di campi DataTables. La base `ibDtV2\DatatableField` non
estende il `DatatableField` legacy e non importa i suoi trait. Parametri e
comportamenti sono definiti nei trait V2 e composti dai tipi specifici.

La tabella usa `Contracts\DatatableFieldContract` per accettare entrambe le
famiglie. `DatatablesFields\FieldFactory` risolve i tipi senza dipendere dalla
base legacy; i vecchi metodi statici di creazione restano disponibili.

Il namespace riservato è `IlBronza\Datatables\DatatablesFields\ibDtV2`.
Il resolver conserva esattamente le maiuscole e minuscole di `ibDtV2`.

Il primo campo disponibile è `ibDtV2.editor.text`:

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
L'entry point include anche `datatables.vendor.editorV2.js`.

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
| `editorRoles` | Ruoli ammessi alla modifica. Senza permessi la tabella mostra testo escaped. |
| `disabled` | Disabilitazione fissa, default `false`. |
| `disabledIf` | Nome di un metodo pubblico del model; viene combinato con `disabled` tramite OR. |
| `saveTrigger` | `enter`, `blur` o `tab`; default dalla configurazione degli editor. |
| `saveButton`, `nullable`, `refreshRow`, `reloadTable`, `spin` | Impostazioni implementate dai trait V2; i default di salvataggio usano la configurazione editor della tabella. |

La lettura segue `forceValue`, poi `inlineEditProperty` nei form inline, poi
`editorValueFunction`, infine `editorProperty` o nome del campo. Il parametro
inviato rimane sempre `parameter` o nome del campo.

Con `disabledIf` viene richiesto il refresh della riga dopo il salvataggio,
così il server ricalcola lo stato. `refreshRow => false` disattiva esplicitamente
questa richiesta; in quel caso lo stato resta quello dell'ultima risposta dati.
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
| `Traits/HtmlAttributesTrait` | Attributi statici/dinamici, escaping e contenitori. |
| `Traits/InputControlTrait` | Input in tabella e nei form inline, stesso contratto degli attributi. |
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
disponibile per il testo sul model della riga, se modificabile e privo di
disabilitazione fissa/condizionale. I target diversi dalla riga non vengono
derivati automaticamente nei form che salvano la riga in blocco.

## Verifiche

`EditorV2TextTest.php` verifica l'assenza di antenati e trait legacy, l'uso
senza tabella, dati, binding, permessi, attributi, forme inline, bulk e una
tabella mista con intestazioni, filtri e CSS Blade. `editorV2.test.js` genera i renderer
eseguendo il PHP reale e li esegue con Node, verificando anche l'aggiornamento
AJAX dei dati. Non usa copie statiche dei renderer.

```sh
node --test tests/editorV2.test.js tests/editorNumeric.test.js tests/inlineEditSave.test.js
```

Il test JavaScript usa `php` e `vendor/autoload.php`; se presente, può usare il
bootstrap condiviso `../.ibtest/bootstrap_datatables.php`. Si possono indicare
un eseguibile PHP e un bootstrap diversi tramite `IB_DT_PHP` e `IB_DT_BOOTSTRAP`.

Durante la migrazione i tipi attuali e quelli nuovi convivono. Al completamento,
i nomi canonici potranno puntare alle nuove implementazioni; solo allora sarà
possibile rimuovere `ibDtV2.` dai valori `type`, dopo aver verificato la
compatibilità dei parametri.
