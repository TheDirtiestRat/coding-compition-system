const assert = require('node:assert/strict');
const fs = require('node:fs');
const vm = require('node:vm');

const elements = new Map();
function element() {
    return {
        value: '', disabled: false, hidden: false, textContent: '', children: [],
        addEventListener() {}, appendChild(child) { this.children.push(child); },
        append(...children) { this.children.push(...children); },
        replaceChildren() { this.children = []; },
    };
}
const document = {
    getElementById(id) {
        if (!elements.has(id)) elements.set(id, element());
        return elements.get(id);
    },
    createElement: element,
    addEventListener(event, listener) { if (event === 'DOMContentLoaded') listener(); },
};
const pending = [];
const context = {
    document, window: { addEventListener() {} }, console, setInterval() {},
    fetch() { return new Promise(resolve => pending.push(resolve)); },
    monaco: { editor: {
        create() { return { setValue() {}, getModel() {}, layout() {} }; },
        setModelLanguage() {},
    } },
};
const template = fs.readFileSync('resources/views/admin/submissions.blade.php', 'utf8');
const source = template.match(/<script>([\s\S]*?)<\/script>/)[1];
vm.runInNewContext(source, context);
const submission = id => ({
    id, submitted_at: id, challenge_language_id: 2, max_points: 10, challenge_language: 'python',
    code: 'print(0)', code_solution: 'print(0)', candidate_name: '<img src=x>',
    test_case_results: [{ passed: false, input: '0', expected_output: '<b>0</b>', actual_output: '0', points: 1 }],
});

(async () => {
    document.getElementById('scoreInput').value = '8';
    const first = context.window.view_submission(1);
    assert.equal(document.getElementById('scoreInput').value, '');
    assert.equal(document.getElementById('scoreInput').disabled, true);
    const second = context.window.view_submission(2);
    pending[1]({ ok: true, json: async () => ({ submission: submission(2) }) });
    await second;
    pending[0]({ ok: true, json: async () => ({ submission: submission(1) }) });
    await first;
    assert.equal(document.getElementById('submittedAt').textContent, 2);
    assert.equal(document.getElementById('candidateName').textContent, '<img src=x>');
    const result = document.getElementById('test_case_results_list').children[0];
    assert.match(result.textContent, /<b>0<\/b>/);
    assert.match(result.textContent, /Input: 0/);
    assert.equal(result.innerHTML, undefined);
    assert.equal(document.getElementById('scoreInput').value, '');

    const queueStart = source.indexOf('            function getSubmissionList(');
    const queueEnd = source.indexOf('            // -----------------------------', queueStart);
    const queueContext = { ...context, submissions: { submission: [] } };
    vm.runInNewContext(source.slice(queueStart, queueEnd) + '\ngetSubmissionList(submissions);', queueContext);
    const queue = document.getElementById('codeSubmissionListElem');
    assert.equal(queue.children.length, 1);
    assert.equal(queue.children[0].textContent, 'No submissions awaiting review.');
    queueContext.submissions.submission.push({ id: 3, candidate_name: '<img src=x>', challenge_title: 'Sum' });
    vm.runInNewContext(source.slice(queueStart, queueEnd) + '\ngetSubmissionList(submissions);', queueContext);
    assert.equal(queue.children.length, 1, 'A populated refresh replaces the empty message.');
    assert.equal(queue.children[0].type, 'button');
    assert.equal(queue.children[0].children[0].textContent, '<img src=x>');
    console.log('Admin selection and text rendering checks passed.');
})();
