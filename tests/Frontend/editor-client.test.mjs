import assert from 'node:assert/strict';
import { readFileSync } from 'node:fs';
import vm from 'node:vm';

const source = readFileSync(new URL('../../resources/js/app.js', import.meta.url), 'utf8')
    .replace('import "./bootstrap";', '');
let initialize;
let fetchCount = 0;
let reloadCount = 0;
const button = {};
const output = {};
const elements = { submit_code_btn: button, code_output_area: output };
const context = vm.createContext({
    document: {
        addEventListener: (event, callback) => { initialize = callback; },
        getElementById: id => elements[id] || null,
        querySelector: () => ({ getAttribute: () => 'token' }),
    },
    fetch: async (url, options) => {
        fetchCount++;
        assert.equal(options.headers.Accept, 'application/json');
        assert.equal(button.disabled, true);
        return { ok: false, status: 422, json: async () => ({ errors: { code: ['Code is required.'] } }) };
    },
    window: { location: { reload: () => { reloadCount++; } } },
});
vm.runInContext(source, context);
await initialize();
assert.equal(fetchCount, 0, 'non-editor pages must not fetch languages or initialize Monaco');
await context.submitCodeForReview('', '1');
assert.equal(output.textContent, 'Submission failed: Code is required.');
assert.equal(button.disabled, false);
assert.equal(reloadCount, 0, 'failed submissions must preserve the editor');
const code = '    print("hello")\n\n';
elements.submittedCode = { value: code };
context.restoredCode = null;
vm.runInContext('editorInstance = { setValue: value => { restoredCode = value; } }; initWithSubmittedCode();', context);
assert.equal(context.restoredCode, code, 'submitted code whitespace must survive initialization');
console.log('Editor initialization, validation feedback and code preservation checks passed.');
