const assert = require('node:assert/strict');
const fs = require('node:fs');
const test = require('node:test');
const vm = require('node:vm');

const source = fs.readFileSync('resources/assets/js/datatables.vendor.fieldOperations.js', 'utf8');

function node(classes = [], attrs = {})
{
    return {classes: new Set(classes), attrs, data: {}, children: []};
}

function boot(ajaxResult = {}, confirmResult = true)
{
    const document = {};
    const handlers = [];
    const calls = [];
    const confirmations = [];

    class Wrapper
    {
        constructor(nodes) { this.nodes = nodes; this[0] = nodes[0]; }
        each(callback) { this.nodes.forEach(el => callback.call(el)); return this; }
        children() { return new Wrapper(this.nodes.flatMap(el => el.children)); }
        filter(callback) { return new Wrapper(this.nodes.filter(el => callback.call(el))); }
        find(selector) {
            assert.equal(selector, 'a[href]');
            return this.children().filter(function() { return this.attrs.href !== undefined; });
        }
        attr(name, value) {
            if (value === undefined) return this[0].attrs[name];
            return this.each(function() { this.attrs[name] = value; });
        }
        removeAttr(name) { return this.each(function() { delete this.attrs[name]; }); }
        data(name, value) {
            if (value === undefined) return this[0].data[name];
            return this.each(function() { this.data[name] = value; });
        }
        removeData(name) { return this.each(function() { delete this.data[name]; }); }
        addClass(names) { return this.each(function() { names.split(' ').forEach(name => this.classes.add(name)); }); }
        hasClass(name) { return this[0].classes.has(name); }
        empty() { return this.each(function() { this.children = []; }); }
        append(child) { return this.each(function() { this.children.push(...child.nodes); }); }
        closest(selector) {
            assert.equal(selector, '[data-cellindex], td');
            return new Wrapper([this[0].owner]);
        }
    }

    function $(target, attributes)
    {
        if (target === document) return {ready(callback) { callback($); }};
        if (target === 'body') return {on(event, selector, callback) { handlers.push(callback); }};
        if (target === '<i/>') return new Wrapper([node(attributes.class.split(' '))]);
        return new Wrapper([target]);
    }

    const window = {
        replace_model_id_string: '__ID__',
        confirm(message) { confirmations.push(message); return confirmResult; },
        ibCallAjax(params) { calls.push(params); if (ajaxResult instanceof Error) throw ajaxResult; return ajaxResult; },
    };
    vm.runInNewContext(source, {window, document, jQuery: $, $});

    function toolbar(id, reminded, operationParameters = {})
    {
        const owner = node();
        const container = node();
        const remind = node(['remind'], {href: '/products/__ID__/update', 'uk-icon': 'plus'});
        const invert = node(['invert'], {href: '/products/__ID__/update'});
        const pdf = node(['pdf'], {href: '/products/__ID__/pdf?id=__ID__'});
        container.children = [remind, invert, pdf];
        container.children.forEach(control => { control.owner = owner; });
        const operations = {
            remind: {type: 'editor.toggle', editorProperty: 'reminding', fetcher: {url: '/products/__ID__/reminds'}},
            invert: {type: 'editor.ajax', editorProperty: 'id', data: {ibaction: 'invertColors'}},
        };
        Object.entries(operationParameters).forEach(([name, parameters]) => {
            operations[name] = Object.assign({}, operations[name], parameters);
        });
        const item = [id, 'Product ' + id, {remind: reminded}];
        window.ibDtInitFieldOperations($(container), item, operations);
        return {owner, remind, invert, pdf, item};
    }

    function click(control)
    {
        const event = {
            defaultPrevented: false,
            propagationStopped: false,
            immediatePropagationStopped: false,
            preventDefault() { this.defaultPrevented = true; },
            stopPropagation() { this.propagationStopped = true; },
            stopImmediatePropagation() { this.immediatePropagationStopped = true; this.stopPropagation(); },
        };
        for (const handler of handlers)
        {
            handler.call(control, event);
            if (event.immediatePropagationStopped)
                break;
        }
        return event;
    }

    return {calls, confirmations, toolbar, click, onClick(handler) { handlers.push(handler); }};
}

test('each product gets its own links, summary URL, and reminder state', () => {
    const runtime = boot();
    const first = runtime.toolbar('first', true);
    const second = runtime.toolbar('second', false);

    assert.equal(first.pdf.attrs.href, '/products/first/pdf?id=first');
    assert.equal(second.pdf.attrs.href, '/products/second/pdf?id=second');
    assert.equal(second.remind.data.fetchurl, '/products/second/reminds');
    assert.ok(first.remind.children[0].classes.has('fa-check'));
    assert.ok(second.remind.children[0].classes.has('fa-close'));
    assert.ok(second.remind.classes.has('clickfetchermodal'));

    runtime.click(second.remind);
    const request = runtime.calls[0];
    assert.equal(request.target, second.owner);
    assert.equal(request.target.data.url, '/products/second/update');
    assert.equal(request.data.field, 'reminding');
    assert.equal(request.data.value, true);
    assert.equal(request.data._method, 'PUT');
    assert.equal(request.data['ib-editor'], true);
    assert.equal(request.refreshRowOnSuccess, true);
    assert.deepEqual(second.item, ['second', 'Product second', {remind: false}]);
    assert.ok(! request.target.classes.has('ib-toggle'), 'The regular toggle handler would overwrite the product label');
});

test('active reminders toggle off and string false stays inactive', () => {
    const runtime = boot();
    runtime.click(runtime.toolbar('first', true).remind);
    runtime.click(runtime.toolbar('second', 'false').remind);
    assert.equal(runtime.calls[0].data.value, false);
    assert.equal(runtime.calls[1].data.value, true);
});

test('ajax actions retain their payload and prevent duplicate requests until completion', () => {
    const runtime = boot();
    const product = runtime.toolbar('second', false);
    runtime.click(product.invert);
    runtime.click(product.invert);
    assert.equal(runtime.calls.length, 1);
    assert.equal(runtime.calls[0].data.ibaction, 'invertColors');
    assert.equal(runtime.calls[0].data.field, 'id');
    assert.equal(runtime.calls[0].target.data.url, '/products/second/update');
    assert.equal(runtime.calls[0].type, 'POST');
    runtime.calls[0].onComplete();
    runtime.click(product.invert);
    assert.equal(runtime.calls.length, 2);
});

test('cancelled and failed requests release the owner for a retry', () => {
    const cancelled = boot(false);
    const first = cancelled.toolbar('first', false);
    cancelled.click(first.invert);
    assert.equal(first.owner.data.editorOperationPending, undefined);

    const failed = boot(new Error('network'));
    const second = failed.toolbar('second', false);
    assert.throws(() => failed.click(second.invert), /network/);
    assert.equal(second.owner.data.editorOperationPending, undefined);
});

for (const name of ['remind', 'invert'])
{
    test(`${name} confirmation cancellation prevents AJAX, pending state, spinner, and other click handlers`, () => {
        const runtime = boot({}, false);
        const message = 'Confermi questa operazione?';
        const product = runtime.toolbar('first', false, {[name]: {confirmMessage: message}});
        let otherClicks = 0;
        runtime.onClick(() => { otherClicks++; });

        const event = runtime.click(product[name]);

        assert.deepEqual(runtime.confirmations, [message]);
        assert.equal(runtime.calls.length, 0);
        assert.equal(product.owner.data.editorOperationPending, undefined);
        assert.equal(product.owner.data.url, undefined);
        assert.equal(product.owner.classes.has('spin'), false);
        assert.equal(product[name].classes.has('spin'), false);
        assert.equal(event.defaultPrevented, true);
        assert.equal(event.propagationStopped, true);
        assert.equal(event.immediatePropagationStopped, true);
        assert.equal(otherClicks, 0);

        runtime.click(product[name]);
        assert.equal(runtime.confirmations.length, 2, 'Cancellation allows another attempt');
        assert.equal(runtime.calls.length, 0);
    });

    test(`${name} confirmation acceptance sends the existing request and starts pending state and spinner`, () => {
        const runtime = boot();
        const message = 'Confermi questa operazione?';
        const product = runtime.toolbar('first', false, {[name]: {confirmMessage: message}});
        let otherClicks = 0;
        runtime.onClick(() => { otherClicks++; });

        runtime.click(product[name]);

        assert.deepEqual(runtime.confirmations, [message]);
        assert.equal(runtime.calls.length, 1);
        assert.equal(product.owner.data.editorOperationPending, true);
        assert.equal(product.owner.classes.has('spin'), true);
        assert.equal(runtime.calls[0].target, product.owner);
        assert.equal(runtime.calls[0].data.field, name === 'remind' ? 'reminding' : 'id');
        assert.equal(runtime.calls[0].data._method, 'PUT');
        assert.equal(runtime.calls[0].data['ib-editor'], true);
        assert.equal(runtime.calls[0].refreshRowOnSuccess, true);
        assert.equal(runtime.calls[0].forceRefreshRowOnSuccess, true);
        assert.equal(runtime.calls[0].deferRowRefresh, true);
        if (name === 'remind')
            assert.equal(runtime.calls[0].data.value, true);
        else
            assert.equal(runtime.calls[0].data.ibaction, 'invertColors');
        assert.equal(otherClicks, 1, 'Accepted clicks keep their existing handlers');
    });
}

test('operations without a confirmation message keep their existing behavior', () => {
    for (const parameters of [{}, {confirmMessage: ''}, {confirmMessage: null}])
    {
        for (const name of ['remind', 'invert'])
        {
            const runtime = boot({}, false);
            const product = runtime.toolbar('first', false, {[name]: parameters});
            let otherClicks = 0;
            runtime.onClick(() => { otherClicks++; });

            runtime.click(product[name]);

            assert.equal(runtime.confirmations.length, 0);
            assert.equal(runtime.calls.length, 1);
            assert.equal(product.owner.data.editorOperationPending, true);
            assert.equal(product.owner.classes.has('spin'), true);
            assert.equal(otherClicks, 1);
        }
    }
});

test('pending clicks do not repeat confirmation, AJAX, or other click handlers in the same cell', () => {
    const runtime = boot();
    const product = runtime.toolbar('first', false, {
        remind: {confirmMessage: 'Confermi il promemoria?'},
        invert: {confirmMessage: 'Invertire i colori?'},
    });
    let otherClicks = 0;
    runtime.onClick(() => { otherClicks++; });

    runtime.click(product.invert);
    const duplicate = runtime.click(product.invert);
    const sibling = runtime.click(product.remind);

    assert.deepEqual(runtime.confirmations, ['Invertire i colori?']);
    assert.equal(runtime.calls.length, 1);
    assert.equal(otherClicks, 1);
    assert.equal(duplicate.immediatePropagationStopped, true);
    assert.equal(sibling.immediatePropagationStopped, true);

    runtime.calls[0].onComplete();
    runtime.click(product.invert);

    assert.deepEqual(runtime.confirmations, ['Invertire i colori?', 'Invertire i colori?']);
    assert.equal(runtime.calls.length, 2);
    assert.equal(otherClicks, 2);
});
