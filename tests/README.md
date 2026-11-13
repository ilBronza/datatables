JavaScript unit tests use Node's built-in runner:

```sh
node --test tests/*.test.js
```

The optional column-style DOM integration tests exercise the real jQuery/DataTables
runtime in jsdom. Install their dependencies outside the package (no browser or
production dependency is added), then run:

```sh
npm install --prefix /tmp/ib-column-style-dom --ignore-scripts jsdom jquery@3.7.1 datatables.net
NODE_PATH=/tmp/ib-column-style-dom/node_modules node --test tests/columnStyles*.test.js
```

PHP tests use the shared `.ibtest` environment documented in the parent `AGENTS.md`.

`RowParentIdTest.php` covers the V2 hierarchy field, Eloquent accessor, strict
JSON normalization and automatic hidden identity. `rowHierarchy.test.js` covers
the iterative forest, 20,000 levels, invalid references and temporary search
expansion. `rowHierarchy.dom.test.js` uses the same jsdom/DataTables/ColReorder
dependencies and `IB_DT_PHP` / `IB_DT_BOOTSTRAP` settings as the V2 editor tests:

```sh
NODE_PATH=/tmp/ib-editor-v2-dom/node_modules node --test tests/rowHierarchy*.test.js
```

It exercises PHP-generated column definitions, single/branch toggles, root
paging, native page selectors, sibling sorting, search context, incremental
patches, AJAX reload, row removal, ColReorder, hidden columns and destroy/recreate.
Optional Select 3 / RowReorder 1 integrations additionally verify hidden selection,
page selection and automatic drag disabling when those extensions are installed.
The hierarchy title-header control is checked across pages, AJAX reloads, search,
flat ordering, scrolling/cloned headers, multiple tables, ColReorder and destroy;
the optional KeyTable case verifies keyboard focus when collapsing all rows.

`fixtures/columnStyles.json` is used by both PHP and JavaScript to keep persisted
CSS and preview normalization consistent.

Column configuration integration tests additionally use ColReorder 2. The tested
host versions are DataTables 2.3.8 and ColReorder 2.1.2. Use jsdom 27 or newer
(older selector engines do not support the existing escaped CSS identifiers):

```sh
npm install --prefix /tmp/ib-table-config-tests --ignore-scripts jsdom@27 jquery@3.7.1 datatables.net@2.3.8 datatables.net-colreorder@2.1.2
TZ=UTC NODE_PATH=/tmp/ib-table-config-tests/node_modules node --test tests/*.test.js
```

The UTC timezone is required by the existing date-range fixtures. The new tests
exercise contextual activation, blocked data actions, all boolean states,
named order persistence/failure, complex headers, visibility, filters, editor
indexes, and width restoration with inline `!important` rules.
They also verify `columnIndexFromOriginal()` with personal order, hidden and
detached headers, scrolling clones, footer filters, restore, invalid references,
and tables with neither ColReorder nor the configurator. The mixed-field PHP
rendering test checks original/current metadata on titles, filters and summaries
and its absence on grouped main headers.

`editorV2.dom.test.js` uses the same DOM dependencies to exercise PHP-generated
V2 fields with real DataTables and ColReorder. It checks deferred URL resolution
for single, bulk and inline saves, missing/zero IDs, hidden/reordered columns and
legacy coexistence. The test uses PHP and the shared bootstrap just like
`editorV2.test.js`; see the V2 field README for `IB_DT_PHP` / `IB_DT_BOOTSTRAP`.
It verifies the object API, legacy context routing, controller isolation and
controller recreation after destroy, and runs history through real AJAX callbacks:
native-vs-saved undo, confirmed values, table isolation, reorder, server-value
conflicts, current custom routes, related target changes, bulk/inline groups,
partial inverse failures, empty responses, overlapping saves and bounded memory.
Textarea cases use the same PHP fixture and DOM harness to check escaped
multiline content, leading/trailing blank lines, native Enter/arrow editing,
blur/Tab saves, inline edit/create batches, bulk controls and undo/redo.
Numeric cases exercise PHP-generated options, exact sorting, formatted vs raw
values, focus/blur without precision loss, invalid-draft retention, single/bulk
and both inline save flows, custom/related routes, undo/redo and legacy prices.
`numericHelpers.test.js` separately checks exact decimal parsing, display rounding,
large integers, tiny fractions, min/max, significant decimal limits and step.
`EditorNumericTest.php` covers string serialization and PHP option validation.

`fixtures/table-configurator.html` is a browser test harness using the real
modules and UIkit, with mock saves and fictional data. Serve the package assets
at `/resources/assets`, the host application's dependencies at `/deps`, and
compiled `resources/assets/css/datatables.less` at `/compiled.css` to use it.
The displayed measurements must read 300 → 150 → 300 px for the customer column
and its scrolling header after edit and reset. Also check alignment, wrapping,
ellipsis, tooltip, boolean icon preview, and keyboard reorder in the browser.
