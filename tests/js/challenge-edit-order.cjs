const assert = require('node:assert/strict');
const fs = require('node:fs');
const vm = require('node:vm');

const script = fs.readFileSync('resources/views/admin/challenges.blade.php', 'utf8')
    .match(/<script>([\s\S]*?)<\/script>/)[1]
    .replace(/const challengeDraft = .*;/, 'const challengeDraft = {};')
    .replace(/const draftPhotos = .*;/, 'const draftPhotos = [];');

function setup() {
    function element() {
        const fields = new Map();
        return {
            value: '', disabled: false, children: [], options: [], listeners: {},
            classList: { toggle() {} },
            addEventListener(event, callback) { this.listeners[event] = callback; },
            appendChild(child) { this.children.push(child); },
            querySelector(selector) {
                if (!fields.has(selector)) fields.set(selector, element());
                return fields.get(selector);
            },
            set innerHTML(value) { this.children = []; },
            submit() { this.submitted = true; },
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
    document.getElementById('language').options = [{ value: 'python', dataset: { langId: '10' } }];
    const pending = [];
    const alerts = [];
    const editor = {
        setValue(value) { this.value = value; },
        getValue() { return this.value; },
        getModel() { return {}; },
    };
    const context = vm.createContext({
        document, editor,
        fetch(url) { return new Promise((resolve, reject) => pending.push({ url, resolve, reject })); },
        monaco: { editor: { setModelLanguage(model, language) { editor.language = language; } } },
        alert(message) { alerts.push(message); },
    });
    vm.runInContext(script, context);
    vm.runInContext('editorInstance = editor; attachSubmitChallengeListener();', context);
    return { context, document, pending, alerts, editor };
}

const payload = id => ({
    id, event_id: id + 10, title: `Challenge ${id}`, description: `Description ${id}`,
    difficulty: 'hard', language: 'python', language_id: '10', points: id * 100,
    output_mode: id === 1 ? 'tokens' : 'numeric', output_tolerance: id / 10,
    code_solution: `print(${id})`, photos_list: [{ id, url: `/photo-${id}.png` }],
    test_cases_list: [{ input: `${id}`, expected_output: `${id * 2}`, points: id }],
});
const settle = () => new Promise(setImmediate);
async function succeed(test, index, id) {
    test.pending[index].resolve({ ok: true, json: async () => payload(id) });
    await settle();
}
function submit(test) {
    test.document.getElementById('challenge_form').listeners.submit({ preventDefault() {} });
}
function assertChallenge(test, id, title = `Challenge ${id}`) {
    const get = key => test.document.getElementById(key);
    const expected = payload(id);
    for (const key of ['event_id', 'description', 'difficulty', 'language', 'language_id', 'points', 'output_mode', 'output_tolerance']) {
        assert.equal(get(key).value, expected[key], key);
    }
    assert.equal(get('challenge_id').value, id);
    assert.equal(get('title').value, title);
    assert.equal(test.editor.value, expected.code_solution);
    assert.equal(test.editor.language, expected.language);
    assert.equal(get('codeLanguage').innerText, expected.language);
    assert.equal(get('existing_photos').children.length, 1);
    assert.equal(get('existing_photos').children[0].querySelector('input').value, id);
    assert.equal(get('test_cases_container').children.length, 1);
    assert.equal(get('test_cases_container').children[0].querySelector('[name$="[input]"]').value, `${id}`);
    assert.equal(get('test_cases_container').children[0].querySelector('[name$="[expected_output]"]').value, `${id * 2}`);
    assert.equal(get('test_cases_container').children[0].querySelector('[name$="[points]"]').value, id);
}

(async () => {
    const reversed = setup();
    reversed.context.edit_challenge(1);
    reversed.context.edit_challenge(2);
    await succeed(reversed, 1, 2);
    reversed.document.getElementById('title').value = 'New draft for challenge 2';
    await succeed(reversed, 0, 1);
    assertChallenge(reversed, 2, 'New draft for challenge 2');
    assert.equal(reversed.document.getElementById('challenge_submit_btn').disabled, false);
    submit(reversed);
    assert.equal(reversed.document.getElementById('challenge_form').submitted, true);
    assert.equal(reversed.document.getElementById('code_solution').value, 'print(2)');

    const staleFailure = setup();
    staleFailure.context.edit_challenge(1);
    staleFailure.context.edit_challenge(2);
    await succeed(staleFailure, 1, 2);
    staleFailure.pending[0].reject(new Error('Stale network failure'));
    await settle();
    assert.deepEqual(staleFailure.alerts, []);
    assertChallenge(staleFailure, 2);
    assert.equal(staleFailure.document.getElementById('challenge_submit_btn').disabled, false);

    const loading = setup();
    loading.context.edit_challenge(1);
    await succeed(loading, 0, 1);
    loading.context.edit_challenge(2);
    assert.equal(loading.document.getElementById('challenge_submit_btn').disabled, true);
    submit(loading);
    assert.equal(loading.document.getElementById('challenge_form').submitted, undefined);
    loading.pending[1].resolve({ ok: false });
    await settle();
    assert.deepEqual(loading.alerts, ['Failed to load challenge.']);
    assert.equal(loading.document.getElementById('challenge_submit_btn').disabled, true);
    submit(loading);
    assert.equal(loading.document.getElementById('challenge_form').submitted, undefined);
    loading.context.edit_challenge(2);
    await succeed(loading, 2, 2);
    assertChallenge(loading, 2);
    assert.equal(loading.document.getElementById('challenge_submit_btn').disabled, false);
    submit(loading);
    assert.equal(loading.document.getElementById('challenge_form').submitted, true);

    const staleDuringLoad = setup();
    staleDuringLoad.context.edit_challenge(1);
    staleDuringLoad.context.edit_challenge(2);
    await succeed(staleDuringLoad, 0, 1);
    assert.equal(staleDuringLoad.document.getElementById('challenge_id').value, '');
    assert.equal(staleDuringLoad.document.getElementById('challenge_submit_btn').disabled, true);
    staleDuringLoad.pending[1].reject(new Error('Newest network failure'));
    await settle();
    assert.deepEqual(staleDuringLoad.alerts, ['Newest network failure']);
    submit(staleDuringLoad);
    assert.equal(staleDuringLoad.document.getElementById('challenge_form').submitted, undefined);
    console.log('Challenge edit ordering, stale errors, loading/failure submission and retry checks passed.');
})().catch(error => { console.error(error); process.exitCode = 1; });
