# TODO personale

Attività da svolgere su questo progetto, distinte dai TODO tecnici della documentazione.

## Da fare

- [ ] Fare in modo che `inlineCreate` e `inlineEdit` si risolvano automaticamente, inserendo un trait nei controller di create ed edit.

- [ ] **Merge ricorsivo del config dei package.** Oggi `mergeConfigFrom` di Laravel fa un `array_merge` shallow: il config pubblicato dall'app sostituisce in blocco gli array di primo livello del package invece di fondersi chiave per chiave. Va sostituito con una versione ricorsiva.

  **Dove:** non in `DatatablesServiceProvider` ma in `IlBronzaServiceProviderPackagesTrait` (`ilbronza/crud`), così vale per tutta la famiglia di package.

  **Decisione aperta, da prendere prima di scrivere il codice:** gli array a chiavi numeriche si fondono o si sostituiscono in blocco? `array_merge` sulle chiavi numeriche *appende*, quindi con il merge ricorsivo un'app non può più accorciare una lista, solo allungarla. In `config/datatables.php` oggi non ci sono liste a indici numerici (`defaultRoles` e `routeRoles` sono array vuoti, il resto è associativo), quindi la scelta non è urgente ma va fatta consapevolmente.

  **Da sistemare prima dello switch:** `editor.saveTrigger` diverge fra codice e config. Nel codice `config('datatables.editor.saveTrigger', 'enter')`, nel config del package `env('DATATABLES_EDITOR_SAVE_TRIGGER', 'blur')`. Con lo shallow, un'app che pubblica un `editor` parziale senza quella chiave prende `enter`; con il merge ricorsivo prenderebbe `blur`, cambiando comportamento in silenzio. È l'unica divergenza reale sui 27 punti in cui il package chiama `config('datatables.…', $default)` — gli altri o coincidono o sono chiavi assenti dal config del package (`defaultButtons.doubler`, `editor.refreshRow`, `editor.reloadTable`).

  **Altre due cose da non dimenticare:** ricopiare la guardia `configurationIsCached()` che ha il `mergeConfigFrom` originale, altrimenti in produzione con `config:cache` si rifà il merge a ogni boot; e usare `array_key_exists` invece di `isset` nella ricorsione, altrimenti un valore messo deliberatamente a `null` dall'app viene scavalcato dal default del package.

  **Effetto collaterale accettato:** con il merge ricorsivo "cancello la riga dal config pubblicato per tornare al default" smette di funzionare, il valore del package risale sempre. Per spegnere qualcosa va messo esplicitamente a `false`.

## In corso

- [ ]

## Completate

- [ ]
