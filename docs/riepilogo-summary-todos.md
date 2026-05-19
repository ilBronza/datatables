# Riepilogo colonne (summary) — TODO

Documento di lavoro per il riepilogo in thead (`tr.summary` / `tr.inlinesearchsummary`) e la gestione dei numeri formattati (es. `53.851,30`).

**File principali**

| Area | File |
|------|------|
| Motore JS | `resources/js/datatables.vendor.summary.min.js` |
| Toggle bottone | `resources/js/datatables.vendor.buttons.min.js` |
| Righe thead riepilogo | `resources/views/uikit/header/_headerSummary.blade.php` |
| Filtri / `data-summary` | `resources/views/datatablesFields/filters/_text.blade.php` (e analoghi) |
| Config summary campi | `src/Traits/DatatableFieldsTrait.php`, `DatatablesFieldsSummaryTrait.php` |
| Field numerici | `src/DatatablesFields/Numbers/DatatableFieldPrice.php`, `DatatableFieldFormatted.php`, `DatatableFieldNumeric.php` |
| Attributi `th` | `resources/views/uikit/header/__headerFilter.blade.php` + `getHeaderData()` |

---

## Fatto

- [x] Autodetect separatore decimale: ultime 2 cifre + simbolo immediatamente a sinistra (`53.851,30` → `,`; `53851.30` → `.`).
- [x] Parse stringhe con separatori multipli ignorati (tranne `-` e separatore decimale rilevato).
- [x] `sum` / `average` su colonne con `data-summary` in thead.
- [x] Formattazione output riepilogo in thead (`formatLocalizedNumber`) allineata al formato rilevato.
- [x] Scansione di tutte le righe per recuperare `thousandsSep` da una cella che lo ha.
- [x] Fallback display: se decimale `,` → migliaia `.`; se decimale `.` → migliaia `,`.
- [x] Ricalcolo su selezione righe (`select.dt` / `deselect.dt`).

---

## Da fare

### 1. Separator espliciti da PHP (priorità alta)

Esporre sul `th` del filtro (via `getHeaderData()` / `parseFieldSpecificHeaderData()`) gli attributi quando il field ha summary numerico:

- `data-summary-decimal-separator` (es. `,`)
- `data-summary-thousands-separator` (es. `.`)
- `data-summary-fraction-digits` (es. `2`)

**PHP**

- [ ] Trait (es. su `DatatableFieldBaseNumber` + `DecimalsTrait`) che imposta `headerData` solo se `getSummaryType()` è `sum` o `average`.
- [ ] `DatatableFieldPrice` — già ha `decimalSeparator`, `thousandsSeparator`, `decimals`.
- [ ] `DatatableFieldFormatted` — stesse proprietà; oggi `transformValue` restituisce il numero grezzo e formatta in JS (`toLocaleString`): gli attributi PHP devono comunque riflettere il formato **visibile**.
- [ ] `DatatableFieldNumeric` — oggi usa `number_format()` senza separatori espliciti; decidere se aggiungere `,` / `.` come gli altri field o lasciare solo autodetect.

**JS**

- [ ] In `populateFilteredColumnValues`, leggere il formato da `th[data-column="N"]` prima dell’autodetect.
- [ ] Strategia **ibrida**: attributi PHP se presenti, altrimenti autodetect attuale.

### 2. Robustezza autodetect (priorità media)

- [ ] Valori con una sola cifra decimale (`100,5`) — oggi l’autodetect richiede 2 cifre finali.
- [ ] Valori interi senza decimali (`1000`) — nessun autodetect; verificare se serve arrotondamento/display coerente.
- [ ] Celle con HTML / suffissi (`1.253,68 €`) — valutare strip lato parse se compaiono in `cell().data()` (non usare `render('display')` salvo decisione esplicita).
- [ ] Array `[id, value]` — già gestiti; verificare su colonne reali.

### 3. Allineamento tipi field (priorità media)

- [ ] Verificare che COSTI / RICAVI / MARGINE usino lo stesso field class o lo stesso set di proprietà separatori.
- [ ] Se alcune colonne usano `DatatableFieldFormatted` con dato grezzo in JSON, valutare `number_format` in PHP come `DatatableFieldPrice` per coerenza dati + summary.

### 4. Test e build (priorità alta prima del deploy)

- [ ] Ricompilare asset (`npm run dev` / `prod`) dopo ogni modifica a `datatables.vendor.summary.min.js`.
- [ ] Test manuale: 3+ colonne `sum` con formato IT, colonne con float US (`53851.30`), selezione righe, filtro inline, righe `summary` vs `inlinesearchsummary`.
- [ ] Test con totali grandi (verifica punti migliaia su tutte le colonne, non solo l’ultima).

### 5. Opzionale / non in scope immediato

- [ ] Tipi summary dedicati (`sumIt`, `averageIt`) — probabilmente **non necessari** se PHP + ibrido funzionano.
- [ ] Riattivare lato server `calculateDataWithSummary()` in `DatatableDataTrait` — oggi commentato; solo se serve riepilogo senza JS.
- [ ] `countDistinct` / `distinct` con numeri formattati — comportamento da definire.

---

## Note di design

1. **Calcolo vs display**: il bug “solo ultima colonna formattata bene” era sul **display** (`thousandsSep` null perché la prima cella era tipo `5014,72`), non sul calcolo. Il fallback `,` → migliaia `.` mitiga il problema in JS; gli attributi PHP lo rendono deterministico.

2. **`getHeaderData()`** è il punto giusto per i separatori: già renderizzato in `__headerFilter.blade.php`; il JS legge già `data-column` da `thead tr.columns th`.

3. **Non duplicare** la logica di formattazione in tre posti (PHP `transformValue`, columnDef `toLocaleString`, summary JS): ideale che summary legga i separatori dal field PHP.

---

## Snippet debug (browser)

Sostituire `ID_TABELLA` e gli indici colonna:

```javascript
const api = $('#ID_TABELLA').DataTable();
[3, 5, 7].forEach(function (i) {
    const th = $('#ID_TABELLA thead tr.columns th[data-column="' + i + '"]');
    const cell = api.cell(0, i);
    console.log('col', i, {
        summary: th.find('input').data('summary'),
        decimalSep: th.data('summaryDecimalSeparator'),
        thousandsSep: th.data('summaryThousandsSeparator'),
        fractionDigits: th.data('summaryFractionDigits'),
        data: cell.data(),
        typeof: typeof cell.data()
    });
});
```

(jQuery converte `data-summary-decimal-separator` in `.data('summaryDecimalSeparator')`.)

---

## Cronologia rapida

| Data | Nota |
|------|------|
| 2026-05-18 | Autodetect decimale + formattazione thead; fix `thousandsSep` su tutte le colonne; discussione attributi PHP su `th`. |
