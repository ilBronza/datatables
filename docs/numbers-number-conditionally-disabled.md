# `numbers.numberConditionallyDisabled`

Questo editor estende `editor.numeric` e disabilita l'input in base al model della
singola riga, mantenendo le opzioni dell'editor numerico, inclusa `digits`.

```php
'quantity' => [
    'type' => 'numbers.numberConditionallyDisabled',
    'conditionallyDisabledMethod' => 'isQuantityDisabled',
    'digits' => 2,
],
```

Il metodo è obbligatorio, viene chiamato senza argomenti sul model e deve
restituire `true` quando l'input deve avere l'attributo HTML `disabled`.

```php
public function isQuantityDisabled(): bool
{
    return $this->status === 'closed';
}
```

Il risultato è il terzo elemento dei dati JSON della cella:
`[idRiga, valoreNumerico, disabilitato]`. Funziona anche nella modifica inline. La
modifica massiva è esclusa, poiché la condizione cambia da riga a riga.

L'attributo `disabled` blocca l'interazione nel browser. Per vietare anche
gli aggiornamenti inviati direttamente al server, applicare la stessa regola
nell'autorizzazione o nella validazione della route di aggiornamento.
