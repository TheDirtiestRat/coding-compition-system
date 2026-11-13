import assert from 'node:assert/strict';
import { readFileSync } from 'node:fs';
import vm from 'node:vm';

class Form extends EventTarget {
    submissions = 0;

    submit() {
        this.submissions++;
    }
}

class Dialog extends EventTarget {
    returnValue = '';

    showModal() {
        this.open = true;
    }

    close(value = '') {
        this.open = false;
        this.returnValue = value;
        this.dispatchEvent(new Event('close'));
    }
}

const dialog = new Dialog();
const forms = [new Form(), new Form()];
const document = new EventTarget();
document.getElementById = (id) => id === 'alert_modal_1' ? dialog : {};
document.querySelectorAll = () => forms;
vm.runInNewContext(readFileSync(new URL('../../resources/js/admin.js', import.meta.url), 'utf8'), { document });
document.dispatchEvent(new Event('DOMContentLoaded'));
const submit = (form) => form.dispatchEvent(new Event('submit', { cancelable: true }));

submit(forms[0]);
submit(forms[0]);
dialog.close('cancel');
await Promise.resolve();
assert.equal(forms[0].submissions, 0);

submit(forms[1]);
dialog.close('accept');
await Promise.resolve();
assert.equal(forms[0].submissions, 0);
assert.equal(forms[1].submissions, 1);

submit(forms[0]);
assert.equal(dialog.returnValue, '');
dialog.close();
await Promise.resolve();
assert.equal(forms[0].submissions, 0);

submit(forms[0]);
dialog.close('accept');
await Promise.resolve();
assert.equal(forms[0].submissions, 1);
console.log('Canceled, repeated and accepted confirmation checks passed.');
