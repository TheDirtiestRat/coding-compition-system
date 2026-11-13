const assert = require('node:assert/strict');
const fs = require('node:fs');
const vm = require('node:vm');

const template = fs.readFileSync('resources/views/admin/challenges.blade.php', 'utf8');
const script = template.match(/<script>([\s\S]*?)<\/script>/)[1]
    .replace(/const challengeDraft = .*;/, 'const challengeDraft = {};')
    .replace(/const draftPhotos = .*;/, 'const draftPhotos = [];');

function element() {
    const fields = new Map();
    return {
        value: '', files: [], children: [], options: [], listeners: {},
        classList: { toggle() {} },
        addEventListener(event, callback) { this.listeners[event] = callback; },
        appendChild(child) { this.children.push(child); },
        querySelector(selector) {
            const key = selector.startsWith('input') ? 'input' : selector;
            if (!fields.has(key)) fields.set(key, element());
            return fields.get(key);
        },
        set innerHTML(value) { this.children = []; },
    };
}

const elements = new Map();
const document = {
    getElementById(id) {
        if (!elements.has(id)) elements.set(id, element());
        return elements.get(id);
    },
    createElement: element,
    addEventListener() {},
};
document.getElementById('language').options = [{ value: 'python', dataset: { langId: '1' } }];
// A real file input clears its FileList when its value is reset.
const photos = document.getElementById('photos');
Object.defineProperty(photos, 'value', {
    set(value) {
        assert.equal(value, '');
        this.files = [];
    },
});
const pending = [];
const alerts = [];
const fakeEditor = { setValue() {}, getModel() { return {}; } };
const context = vm.createContext({
    document, fakeEditor,
    fetch: () => new Promise(resolve => pending.push(resolve)),
    monaco: { editor: { setModelLanguage() {} } },
    alert: message => alerts.push(message),
});
vm.runInContext(script, context);
vm.runInContext('editorInstance = fakeEditor;', context);

const payload = id => ({
    id, event_id: 1, title: `Challenge ${id}`, description: '', difficulty: 'easy',
    language: 'python', language_id: 1, points: 10, code_solution: '',
    photos_list: [{ id: id * 10, url: `/storage/${id}.png` }], test_cases_list: [],
});
const settle = () => new Promise(setImmediate);
const preview = document.getElementById('photo_preview_container');
function selectUpload(name) {
    const file = { name };
    photos.files = [file];
    preview.children = [file];
    return file;
}

(async () => {
    document.getElementById('challenge_id').value = '1';
    selectUpload('for-challenge-1.png');
    context.edit_challenge(2);
    assert.equal(photos.files.length, 1, 'Keep the current draft while loading.');
    pending.shift()({ ok: true, json: async () => payload(2) });
    await settle();
    assert.equal(document.getElementById('challenge_id').value, 2);
    assert.equal(photos.files.length, 0, 'Uploads from challenge 1 must not be submitted for challenge 2.');
    assert.equal(preview.children.length, 0, 'Clear previews of discarded uploads.');

    const existing = document.getElementById('existing_photos').children;
    assert.equal(existing.length, 1);
    assert.equal(existing[0].querySelector('img').src, '/storage/2.png');
    assert.equal(existing[0].querySelector('input').value, 20);
    assert.equal(existing[0].querySelector('input').checked, false);
    existing[0].listeners.click.call(existing[0], { preventDefault() {} });
    assert.equal(existing[0].querySelector('input').checked, true, 'Existing-photo deletion remains usable.');

    const selected = selectUpload('for-challenge-2.png');
    document.getElementById('challenge_id').value = '2';
    context.edit_challenge(2);
    pending.shift()({ ok: true, json: async () => payload(2) });
    await settle();
    assert.equal(photos.files[0], selected, 'Reloading the same target preserves selected uploads.');
    assert.equal(preview.children[0], selected);

    context.edit_challenge(3);
    pending.shift()({ ok: false });
    await settle();
    assert.equal(photos.files[0], selected, 'Failed loads preserve current uploads.');
    assert.equal(preview.children[0], selected);
    assert.equal(alerts.length, 1);
    console.log('Challenge target switches clear new uploads; same-target reloads and existing photos remain usable.');
})().catch(error => { console.error(error); process.exitCode = 1; });
