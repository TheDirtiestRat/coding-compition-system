import assert from 'node:assert/strict';
import { readFileSync } from 'node:fs';
import test from 'node:test';
import vm from 'node:vm';

const script = readFileSync(new URL('../../public/js/admin-theme.js', import.meta.url), 'utf8');

function page(saved = null, blocked = false) {
    const root = { dataset: { theme: 'dark' } };
    const label = {};
    const attributes = {};
    const writes = [];
    const editorThemes = [];
    let ready, click;
    const button = {
        hidden: true,
        querySelector: () => label,
        setAttribute: (key, value) => { attributes[key] = value; },
        addEventListener: (_event, handler) => { click = handler; },
    };
    vm.runInNewContext(script, {
        document: { documentElement: root, querySelector: () => button,
            addEventListener: (_event, handler) => { ready = handler; } },
        window: { monaco: { editor: { setTheme: theme => editorThemes.push(theme) } } },
        localStorage: {
            getItem(key) {
                assert.equal(key, 'coding-admin-theme');
                if (blocked) throw new Error('Storage unavailable');
                return saved;
            },
            setItem(key, value) {
                if (blocked) throw new Error('Storage unavailable');
                writes.push([key, value]);
            },
        },
    });
    return { root, label, attributes, button, writes, editorThemes, ready: () => ready(), click: () => click() };
}

test('saved theme is applied before controls initialize and survives a later visit', () => {
    const p = page('light');
    assert.equal(p.root.dataset.theme, 'light');
    assert.equal(p.button.hidden, true);
    p.ready();
    assert.equal(p.button.hidden, false);
    assert.equal(p.attributes['aria-label'], 'Switch to dark theme');
    p.click();
    assert.equal(p.root.dataset.theme, 'dark');
    assert.equal(p.label.textContent, 'Light mode');
    assert.deepEqual(p.writes, [['coding-admin-theme', 'dark']]);
    assert.deepEqual(p.editorThemes, ['vs-dark']);
    assert.equal(page(p.writes[0][1]).root.dataset.theme, 'dark');
    p.click();
    assert.equal(p.root.dataset.theme, 'light');
    assert.equal(p.label.textContent, 'Dark mode');
    assert.equal(p.editorThemes.at(-1), 'vs');
});

test('missing or invalid saved values keep the approved dark default', () => {
    for (const saved of [null, '', 'system', '<invalid>']) {
        assert.equal(page(saved).root.dataset.theme, 'dark');
    }
});

test('blocked browser storage does not prevent switching themes', () => {
    const p = page(null, true);
    p.ready();
    p.click();
    assert.equal(p.root.dataset.theme, 'light');
    assert.equal(p.attributes['aria-label'], 'Switch to dark theme');
    assert.deepEqual(p.editorThemes, ['vs']);
    assert.deepEqual(p.writes, []);
});
