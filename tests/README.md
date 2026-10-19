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
