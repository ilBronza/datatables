# Fields-group toggle button

`FieldsGroupToggleButton` is a declarative DataTables button for one logical
group of columns. The group remains opt-in on each field: declaring a button
does not add fields to it and does not infer a `texts` group from an editor
type.

```php
use IlBronza\Datatables\Buttons\FieldsGroupToggleButton;

$datatable->addButton(new FieldsGroupToggleButton('economic_minor_details', [
    'activeText' => 'Mostra dettagli economici',
    'inactiveText' => 'Nascondi dettagli economici',
    'icon' => 'coins',
    'classes' => ['uk-button', 'uk-button-default'],
    'attributes' => ['title' => 'Mostra o nascondi i dettagli economici'],
]));

$datatable->addButton(new FieldsGroupToggleButton('texts', [
    'activeText' => 'Mostra testi',
    'inactiveText' => 'Nascondi testi',
    'icon' => 'align-left',
]));
```

`addButton()` continues to receive a real `Button` object and supplies the
table id as usual. The renderer produces a DataTables definition equivalent to:

```js
{
    extend: 'fieldsGroupToggle',
    fieldGroup: 'economic_minor_details'
}
```

Declare the membership explicitly in the field parameters:

```php
'fieldsGroupsDefinitions' => ['economic_minor_details']
// or
'fieldsGroupsDefinitions' => ['texts']
```

Each group button remembers only the columns it hid itself. Its next click
restores that subset, leaving columns that were already hidden untouched. If a
group is entirely hidden by saved preferences, its first click shows the group.
The button manages `uk-button-primary`, `aria-pressed`, and the configured
`activeText` / `inactiveText` automatically. `text` remains a fallback when
one or both state-specific labels are omitted. “Active” means that the group
is currently hidden, so the usual labels are `activeText: 'Mostra testi'` and
`inactiveText: 'Nascondi testi'`.

The existing `fieldsGroups` dropdown remains available; its menu items call the
same internal `window.ibDatatableFieldsGroups` API as the declarative button.
