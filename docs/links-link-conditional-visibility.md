# Visibilità condizionale di `links.link`

Il parametro opzionale `conditionallyDisabledMethod` indica un metodo del model
della riga. Se restituisce `true`, la cella resta vuota e il metodo che genera
l'URL non viene chiamato. Se restituisce `false`, il link viene mostrato normalmente.

```php
'order' => [
    'type' => 'links.link',
    'method' => 'getShowUrl',
    'textParameter' => 'label',
    'conditionallyDisabledMethod' => 'isLinkDisabled',
],
```

```php
public function isLinkDisabled(): bool
{
    return $this->status === 'closed';
}
```

`conditionallyDisabledMethod` viene chiamato senza argomenti per ogni model
presente nella cella. Senza il parametro, `links.link` mantiene il comportamento precedente.
La condizione riguarda la visualizzazione: se serve impedire l'accesso diretto,
la route deve applicare la stessa regola.
