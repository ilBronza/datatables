# `editor.selectConditionallyDisabled`

Questo editor estende `editor.select` e può disabilitare il select in base al
model della riga.

```php
'vehicle_id' => [
    'type' => 'editor.selectConditionallyDisabled',
    'conditionallyDisabledMethod' => 'isVehicleSelectDisabled',
],
```

Il metodo è obbligatorio, viene chiamato senza argomenti sul model della riga
e deve restituire `true` quando il select deve avere l'attributo HTML
`disabled`.

```php
public function isVehicleSelectDisabled(): bool
{
    return $this->status === 'closed';
}
```

Il risultato è il quarto elemento dei dati JSON della cella:
`[idRiga, valore, etichetta, disabilitato]`. Il tipo supporta anche la riga
di modifica inline. La modifica massiva è esclusa, poiché la condizione cambia
da riga a riga.

L'attributo `disabled` blocca l'interazione nel browser. Per vietare anche
gli aggiornamenti inviati direttamente al server, applicare la stessa regola
nell'autorizzazione o nella validazione della route di aggiornamento.
