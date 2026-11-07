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

`fixtures/table-configurator.html` is a browser test harness using the real
modules and UIkit, with mock saves and fictional data. Serve the package assets
at `/resources/assets`, the host application's dependencies at `/deps`, and
compiled `resources/assets/css/datatables.less` at `/compiled.css` to use it.
The displayed measurements must read 300 → 150 → 300 px for the customer column
and its scrolling header after edit and reset. Also check alignment, wrapping,
ellipsis, tooltip, boolean icon preview, and keyboard reorder in the browser.
