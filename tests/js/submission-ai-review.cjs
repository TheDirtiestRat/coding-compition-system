const assert = require('node:assert/strict');
const fs = require('node:fs');
const vm = require('node:vm');

const elements = new Map();
function element() {
    return {
        textContent: '', hidden: true, disabled: true, children: [], dataset: {}, listeners: {},
        addEventListener(event, handler) { this.listeners[event] = handler; },
        replaceChildren() { this.children = []; },
        appendChild(child) { this.children.push(child); },
    };
}
const document = {
    getElementById(id) {
        if (!elements.has(id)) elements.set(id, element());
        return elements.get(id);
    },
    querySelector() { return { content: 'csrf' }; },
    createElement: element,
};
const button = document.getElementById('aiReviewButton');
button.dataset.enabled = 'true';
const pending = [];
const timers = [];
const context = {
    document, window: {}, Date,
    fetch(url, options) { return new Promise(resolve => pending.push({ url, options, resolve })); },
    setTimeout(handler) { timers.push(handler); return timers.length; },
    clearTimeout() {},
};
vm.runInNewContext(fs.readFileSync('public/js/submission-ai-review.js', 'utf8'), context);
const respond = (request, body, ok = true) => request.resolve({ ok, json: async () => body });

(async () => {
    context.window.setAiReviewSubmission({ id: 1, codeHash: 'first' });
    assert.equal(button.disabled, false);
    const oldRequest = button.listeners.click();
    context.window.setAiReviewSubmission({ id: 2, codeHash: 'second' });
    respond(pending.shift(), { review_id: 'old-review' });
    await oldRequest;
    assert.equal(pending.length, 0, 'Switching selections discards a late queued response.');
    assert.equal(button.disabled, false);

    const request = button.listeners.click();
    const post = pending.shift();
    assert.equal(JSON.parse(post.options.body).code_hash, 'second');
    respond(post, { review_id: 'new-review' });
    await new Promise(resolve => setImmediate(resolve));
    respond(pending.shift(), { state: 'completed', model: 'local-model', review: {
        summary: '<img src=x onerror=alert(1)>', findings: ['<script>unsafe</script>'], limitations: 'Advisory only.',
    } });
    await request;
    assert.equal(document.getElementById('aiReviewSummary').textContent, '<img src=x onerror=alert(1)>');
    assert.equal(document.getElementById('aiReviewFindings').children[0].textContent, '<script>unsafe</script>');
    assert.equal(document.getElementById('aiReviewSummary').innerHTML, undefined);
    assert.equal(button.disabled, false);
    assert.equal(document.getElementById('aiReviewResult').hidden, false);

    const failed = button.listeners.click();
    respond(pending.shift(), { message: 'Local service unavailable.' }, false);
    await failed;
    assert.equal(document.getElementById('aiReviewStatus').textContent, 'Local service unavailable.');
    assert.equal(button.disabled, false, 'A failed review can be retried.');
    context.window.setAiReviewSubmission(null);
    assert.equal(button.disabled, true);
    assert.equal(document.getElementById('aiReviewResult').hidden, true);
    console.log('Local AI review preserves selection, renders plain text and recovers from failure.');
})();
