# CRUD model broadcasts

Model broadcasts are opt-in. Enable them per table with
`$table->setListenToModelBroadcasts()` or globally with
`DATATABLES_MODEL_BROADCASTS_ENABLED=true`. When enabled, a table with a base
model exposes its CRUD broadcast channel in the markup and registers it when
DataTables emits `init.dt`.

The integration follows the Crud package contract exactly:

- private channel: `channel.crud-events.models.<kebab model basename>`;
- event: `.crud.model.changed`;
- payload: `model`, `key`, `text`, and `action`.

## Broadcast origin

Ajax mutations initiated by a DataTable send their browser-tab and table
identifiers to Crud. Crud returns them in the event as an optional `origin`
object. Datatables ignores an event only when both identifiers match the
receiving table, avoiding the redundant refresh after its own mutation while
still updating other tables and browser tabs.

The identifiers use the request headers
`X-IB-Datatable-Browser-Tab-Id` and `X-IB-Datatable-Table-Id`. They are
correlation metadata only and must never be used for authentication or
authorization.

One Echo subscription is shared by all tables listening to the same model. A
destroyed table is removed from that subscription, so a rendered fragment can
be initialized again without adding another listener.

`updated` events use `reloadTableRows()` to refresh the affected row, then give
the row a short visual flash and highlight the cells whose data changed.
Delete-like events remove the visible row or reload as a fallback.

## Created events

For `action: "created"` the broadcast contains only `model`, `key`, and
`action`. Datatables verifies the model class, then obtains the record from the
backend before changing a table. The event data is never rendered directly.

For the usual Ajax DataTable, Datatables sends `GET` to the table's existing
Ajax URL with `rowId=<event.key>`. This uses the package's existing single-row
response (`{ data: [mappedRow] }`), so the record goes through exactly the same
field mapping and rendering pipeline as normal table data. `rowId` accepts
string keys and is not tied to an `id` primary key.

Client-side tables receive the returned mapped row through
`datatable.row.add(row).draw(false)`. DataTables therefore applies the active
ordering and filters while retaining the current page. With
`serverSide: true`, no local row is added: `datatable.ajax.reload(null, false)`
is debounced instead.

For a client-side table without an Ajax URL, configure either a single-record
endpoint:

```php
$table
    ->setListenToModelBroadcasts()
    ->setModelBroadcastCreatedFetchUrl(route('orders.datatable-row'));
```

The endpoint must accept the key (by default as `rowId`) and return the
DataTables-mapped shape `{ "data": [row] }`. If the endpoint needs a different
URL, parameter name, method, or authentication scheme, configure a JavaScript
request-builder hook:

```php
$table
    ->setListenToModelBroadcasts()
    ->setModelBroadcastCreatedFetchRequestHook('App.datatables.orderRequest');
```

```js
window.App = window.App || {};
window.App.datatables = {
    orderRequest(table, key) {
        return {
            url: '/api/orders/' + encodeURIComponent(key),
            type: 'GET',
            dataType: 'json',
        };
    },
};
```

The hook receives `(table, key, event, datatable)` and must return either a
jQuery Ajax settings object, a URL string, or a Promise resolving to
`{ data: [mappedRow] }`. Repeated events for the same key are deduplicated
while the fetch is pending and after the row is added. If the request or row
format is invalid, the table falls back to a non-destructive reload.

When UIKitTemplate exposes `window.addFooterLog`, each received CRUD event is
also forwarded to that footer log with its model, key, and action.

The default channel is
`channel.crud-events.models.<kebab model basename>`, for example
`channel.crud-events.models/order` for `App\Models\Order`. Override it with
`$table->setModelBroadcastChannel('private-channel-name')` when the Crud
package uses a different private channel. Call
`$table->setListenToModelBroadcasts(false)` to disable a globally enabled
integration.
