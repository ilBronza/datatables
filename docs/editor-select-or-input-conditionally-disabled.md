# `editor.selectOrInputConditionallyDisabled`

Questo editor estende `editor.selectOrInput`. Quando la cella mostra un select
oppure un input, applica `disabled` al controllo mostrato se un metodo del
model della riga restituisce `true`.

```php
'code' => [
    'type' => 'editor.selectOrInputConditionallyDisabled',
    'editorProperty' => 'display_code',
    'conditionallyDisabledMethod' => 'isCodeDisabled',
    'possibleValuesMethod' => 'getCodePossibleValues',
],
```

```php
public function isCodeDisabled(): bool
{
    return $this->status === 'closed';
}
```

Il metodo è obbligatorio e viene chiamato senza argomenti. Il booleano è il
sesto elemento della cella JSON:
`[idRiga, valore, etichetta, testoLibero, mostraSelect, disabilitato]`.
Nella modifica inline, `selectOrInput` mostra un select: anche questo viene
disabilitato quando la condizione è vera. La modifica massiva è esclusa.
