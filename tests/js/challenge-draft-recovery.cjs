const assert = require('node:assert/strict');
const fs = require('node:fs');
const vm = require('node:vm');

const template = fs.readFileSync('resources/views/admin/challenges.blade.php', 'utf8');
const script = template.match(/<script>([\s\S]*?)<\/script>/)[1];
const code = '    print("</script>")\n\n';
const testCase = { input: '  4\n', expected_output: '  8\n', points: '2.5' };

function initialize(draft) {
    function element() {
        const fields = new Map();
        return {
            value: '', children: [], listeners: {}, classList: { toggle() {} },
            addEventListener(event, callback) { this.listeners[event] = callback; },
            appendChild(child) { this.children.push(child); },
            querySelector(selector) {
                // All input selectors on photo wrappers refer to the same checkbox.
                const key = selector.startsWith('input') ? 'input' : selector;
                if (!fields.has(key)) fields.set(key, element());
                return fields.get(key);
            },
            set innerHTML(value) { this.children = []; },
            submit() { this.submitted = true; },
        };
    }
    const elements = new Map();
    let ready;
    let editorOptions;
    const document = {
        getElementById(id) {
            if (!elements.has(id)) elements.set(id, element());
            return elements.get(id);
        },
        createElement: element,
        querySelectorAll: () => [],
        addEventListener(event, callback) { ready = callback; },
    };
    document.getElementById('language').value = 'python';
    document.getElementById('challenge_id').value = '7';
    const source = script
        .replace(/const challengeDraft = .*;/, `const challengeDraft = ${JSON.stringify(draft)};`)
        .replace(/const draftPhotos = .*;/, 'const draftPhotos = [{ id: 9, url: "/storage/old.png" }];');
    vm.runInNewContext(source, {
        document, window: { addEventListener() {} },
        monaco: { editor: {
            create(container, options) {
                editorOptions = options;
                return { getValue: () => options.value, layout() {} };
            },
        } },
    });
    ready();
    return { document, editorOptions };
}

const { document, editorOptions } = initialize({
    challenge_id: '7', code_solution: code, test_cases: { 3: testCase }, delete_photos: ['9'],
});
assert.equal(editorOptions.value, code, 'Monaco must receive exact draft code');
assert.equal(editorOptions.language, 'python');
assert.equal(document.getElementById('codeLanguage').innerText, 'python');
const entries = document.getElementById('test_cases_container').children;
assert.equal(entries.length, 1);
assert.equal(entries[0].querySelector('[name$="[input]"]').value, testCase.input);
assert.equal(entries[0].querySelector('[name$="[expected_output]"]').value, testCase.expected_output);
assert.equal(entries[0].querySelector('[name$="[points]"]').value, testCase.points);
const checkbox = document.getElementById('existing_photos').children[0].querySelector('input');
assert.equal(checkbox.value, 9);
assert.equal(checkbox.checked, true, 'keep deletion intent after validation fails');
const form = document.getElementById('challenge_form');
form.listeners.submit({ preventDefault() {} });
assert.equal(form.submitted, true);
assert.equal(document.getElementById('challenge_id').value, '7');
assert.equal(document.getElementById('code_solution').value, code);
assert.equal(initialize({}).editorOptions.value, '', 'a new Python solution must not start with Java code');
assert.equal(initialize({ code_solution: null }).editorOptions.value, '', 'empty invalid code must stay empty');
assert.equal(initialize({ test_cases_present: '1' }).document.getElementById('test_cases_container').children.length, 0);
console.log('Challenge draft editor, test cases, photo intent and edit resubmission checks passed.');
