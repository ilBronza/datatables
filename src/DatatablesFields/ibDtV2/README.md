# ibDtV2

Cartella della nuova famiglia di campi DataTables, con parametri comuni definiti
nella classe base ed ereditati dai tipi specifici.

Il namespace riservato è `IlBronza\Datatables\DatatablesFields\ibDtV2`.
Il resolver conserva esattamente le maiuscole e minuscole di `ibDtV2`.

Quando saranno implementati, i nuovi campi useranno il prefisso `ibDtV2.` nei
valori `type` dei file dei parametri, per esempio:

```php
'type' => 'ibDtV2.editor.text',
```

Questo tipo risolve la classe
`IlBronza\Datatables\DatatablesFields\ibDtV2\Editor\DatatableFieldText`.
La cartella per ora prepara la nuova famiglia: i tipi specifici devono ancora
essere implementati.

Durante la migrazione i tipi attuali e quelli nuovi convivono. Al completamento,
i nomi canonici potranno puntare alle nuove implementazioni; solo allora sarà
possibile rimuovere `ibDtV2.` dai valori `type`, dopo aver verificato la
compatibilità dei parametri.
