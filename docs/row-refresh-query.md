# Query per il refresh delle righe

Il refresh dopo un salvataggio editor continua a chiamare l'URL AJAX della
tabella con `rowId`. Anche `rowIds` usa lo stesso caricamento selettivo.
Il JSON mantiene il formato DataTables, con le righe aggiornate in `data`.

## Controller con `getIndexQuery()`

Il trait CRUD `CRUDIndexTrait` riconosce il metodo pubblico `getIndexQuery()`.
Il metodo deve restituire un builder Eloquent o una relazione, senza eseguire
`get()`, `first()` o `all()`:

```php
public function getIndexQuery()
{
    return $this->getModelClass()::query()
        ->with('client', 'destination')
        ->active();
}
```

Quando questo metodo esiste, viene usato sia per l'index completo sia per il
refresh: eventuali condizioni e relazioni dell'index devono quindi stare qui.
Il vecchio `getIndexElements()` non viene chiamato. Per il refresh, Datatables
aggiunge un filtro sulla chiave primaria prima di eseguire la query. Il filtro
viene applicato al builder della tabella, senza registrare uno scope globale
sul modello. Il builder originale non viene modificato.

`whereKey()` rispetta chiavi primarie diverse da `id`, chiavi stringa e colonne
qualificate. Le condizioni `OR` della query restano raggruppate sotto il filtro
della riga. Gli scope già presenti restano attivi.

## Index standard

Per adottare il contratto sui controller standard, usare il nuovo trait CRUD:

```php
use IlBronza\CRUD\Http\Controllers\Traits\StandardTraits\PackageStandardQueryIndexTrait;

class OrderIndexController extends OrderCRUD
{
    use PackageStandardQueryIndexTrait;

    public function getIndexElementsRelationsArray(): array
    {
        return ['client', 'destination'];
    }

    public function customizeQuery($query)
    {
        return $query->active();
    }
}
```

Il trait conserva relazioni, scope e `customizeQuery()` del percorso standard.
`PackageStandardIndexTrait` rimane compatibile con il comportamento precedente
e non espone `getIndexQuery()`: i suoi override esistenti non vengono saltati.

## Controller esistenti

In assenza di `getIndexQuery()`, Datatables registra uno scope live sul
`modelClass` della tabella prima di chiamare il provider di `getIndexElements()`.
Lo scope limita le query Eloquent agli ID richiesti e viene rimosso nel
`finally`, anche quando il provider fallisce. Gli scope precedenti vengono
conservati; caricamenti annidati ripristinano il filtro esterno.

Lo scope resta attivo per tutta la chiamata, incluse le relazioni dello stesso
modello. Per controllare il filtro solo sulla query principale, implementare
`getIndexQuery()`.

Un `elementsGetter` esplicito passato a `_index()` mantiene la precedenza sul
nuovo metodo e passa dal percorso legacy. I provider che restituiscono
collection già caricate o usano query prive di scope mantengono il filtro
finale sulla collection; in questi casi il caricamento dal database non può
essere ridotto dallo scope live. Una selezione vuota non chiama il provider;
una riga mancante o esclusa dalle condizioni restituisce `data: []`.

## Tabelle standalone

Il provider della query può essere passato direttamente a Datatables:

```php
$table = Datatables::create(
    name: 'orders',
    fieldsGroups: $fieldsGroups,
    elements: fn () => Order::all(),
    modelClass: Order::class,
    queryProvider: fn () => Order::query()->with('client')
);
```

`createStandAloneTable()` accetta lo stesso callable nella chiave
`queryProvider` del suo array di parametri.
