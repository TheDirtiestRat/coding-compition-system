const assert = require('node:assert/strict');
const fs = require('node:fs');
const vm = require('node:vm');
const path = require('node:path');

const markup = fs.readFileSync(path.join(__dirname, '../../resources/views/authentication/login.blade.php'), 'utf8');
const elements = new Map([...markup.matchAll(/id="([^"]+)"/g)].map(([, id]) => [id, {
    type: id === 'password' ? 'password' : undefined,
    value: id === 'password' ? 'keep this password' : '',
    listeners: {}, attributes: {},
    addEventListener(event, handler) { this.listeners[event] = handler; },
    setAttribute(name, value) { this.attributes[name] = value; },
    focus() {},
}]));
const document = {
    getElementById: id => elements.get(id) ?? null,
    querySelectorAll: () => [],
    addEventListener(event, handler) { if (event === 'DOMContentLoaded') handler(); },
};
const script = [...markup.matchAll(/<script>([\s\S]*?)<\/script>/g)].at(-1)[1];
vm.runInNewContext(script, { document });
const button = elements.get('togglePassword');
button.listeners.click();
assert.equal(elements.get('password').type, 'text');
assert.equal(elements.get('password').value, 'keep this password');
assert.equal(button.textContent, 'Hide');
assert.equal(button.attributes['aria-label'], 'Hide password');
assert.equal(button.attributes['aria-pressed'], 'true');
button.listeners.click();
assert.equal(elements.get('password').type, 'password');
assert.equal(button.textContent, 'Show');
assert.equal(button.attributes['aria-label'], 'Show password');
assert.equal(button.attributes['aria-pressed'], 'false');
assert.equal(button.listeners.keypress, undefined, 'Native buttons already handle keyboard activation.');
console.log('Login password visibility preserves the value and accessible toggle state.');
