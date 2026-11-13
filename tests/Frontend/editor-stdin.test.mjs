import assert from 'node:assert/strict';
import { readFileSync } from 'node:fs';
import vm from 'node:vm';

const source = readFileSync(new URL('../../resources/js/app.js', import.meta.url), 'utf8')
    .replace('import "./bootstrap";', '');
const input = { value: '' };
const output = {};
let request;
const context = vm.createContext({
    document: {
        addEventListener: () => {},
        getElementById: id => ({ code_input: input, code_output_area: output })[id],
        querySelector: () => ({ getAttribute: () => 'token' }),
    },
    fetch: async (url, options) => {
        request = { url, body: JSON.parse(options.body) };
        return { ok: true, json: async () => ({ output: 'done' }) };
    },
});
vm.runInContext(source, context);
const code = '  print(input())\n';
for (const value of ['', '0', '  hello  ', '\n  first\nsecond\n\n']) {
    input.value = value;
    await context.runServerCode(code, '1');
    assert.equal(request.url, '/run-code');
    assert.deepEqual(request.body, { code, language_id: '1', input: value });
    assert.match(output.textContent, /done$/);
}
const view = readFileSync(new URL('../../resources/views/pages/code-editor.blade.php', import.meta.url), 'utf8');
assert.match(view, /<label for="code_input"/);
assert.match(view, /<textarea id="code_input"/);
console.log('Run requests preserve empty, zero, spaces and multiline stdin.');
