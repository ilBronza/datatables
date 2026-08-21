# `DatatableFieldAsync`

`async` non aggiunge dati alla cella e carica il contenuto visibile dopo ogni
`draw.dt`. La configurazione della chiamata è salvata nel `data-*` dell'header.
Gli ID vengono letti dalle righe correnti tramite l'helper comune
`ibDtGetRowIdFromRowIndex()`, già usato dalle altre funzioni DataTables. Questo
helper privilegia l'`id` del nodo `<tr>` e gestisce i fallback del pacchetto.
Il renderer possiede l'intero `<td>` e vi scrive direttamente, senza aggiungere
contenitori interni.

## Configurazione

```php
'asyncAlerts' => [
	'type' => 'async',
	'route' => route('orders.async-alerts'),
	'renderer' => 'alerts',
],
```

`route` è l'URL già risolto. In alternativa si possono passare un nome Laravel
e gli eventuali parametri:

```php
'asyncAlerts' => [
	'type' => 'async',
	'routeName' => 'orders.async-alerts',
	'routeParameters' => ['tenant' => $tenant],
	'renderer' => 'alerts',
],
```

La tabella deve avere il proprio campo `rowId` configurato, come per le altre
funzioni che operano sulle righe. Il campo async non replica quell'ID nel
dataset o nel markup della cella.

La cache è disattivata di default: ogni draw rilegge lo stato corrente. Per
payload immutabili si può impostare `'cache' => true`.

## Contratto HTTP

Per ogni colonna async visibile viene eseguita una sola richiesta:

```http
POST /orders/async-alerts

ids[]=12&ids[]=19&ids[]=27
```

L'endpoint deve autorizzare la richiesta, validare gli ID e recuperarli in
batch, evitando una query per elemento. Deve restituire una mappa indicizzata
per ID, direttamente o dentro `data`:

```json
{
  "12": [{"label": "Indirizzo mancante", "href": "/orders/12", "target": "_blank"}],
  "19": [],
  "27": [{"label": "Prezzo da verificare"}]
}
```

Esempio di controller:

```php
public function __invoke(Request $request)
{
	$validated = $request->validate([
		'ids' => ['required', 'array'],
		'ids.*' => ['integer'],
	]);

	$payload = Order::query()
		->whereKey($validated['ids'])
		->get()
		->mapWithKeys(fn (Order $order) => [
			(string) $order->getKey() => $order->getAlertsForDatatable(),
		]);

	return response()->json((object) $payload->all());
}
```

Gli ID assenti dalla risposta producono una cella vuota.

## Renderer

Sono disponibili `text` (default, con escaping HTML), `html` (contenuto fidato)
e `alerts`. Un renderer applicativo si registra una sola volta:

```javascript
window.ibRegisterAsyncRenderer('statusBadge', function (payload, id) {
	if (! payload)
		return '';

	return '<span class="uk-label">'
		+ window.ibAsyncRenderers.text(payload.label)
		+ '</span>';
});
```

Poi si usa `'renderer' => 'statusBadge'` nella definizione del campo. Un
renderer che produce HTML deve fare escaping dei valori non fidati.

Il runtime è incluso nell'entrypoint compilato `ilBronza.datatables.js`; non
richiede script Blade dedicati.
