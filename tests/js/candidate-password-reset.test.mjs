import assert from 'node:assert/strict';
import { readFileSync } from 'node:fs';
import vm from 'node:vm';

const source = readFileSync(new URL('../../resources/views/admin/candidates.blade.php', import.meta.url), 'utf8');
const fields = new Map();
const document = {
    getElementById(id) {
        if (!fields.has(id)) fields.set(id, { value: '', checked: false });
        return fields.get(id);
    },
};
const start = source.indexOf('        function edit_candidate(');
const end = source.indexOf('        function show_code_solutions(', start);
const context = vm.createContext({ document });
vm.runInContext(source.slice(start, end), context);

context.edit_candidate(1, 10, 'Alice', 'alice');
fields.get('edit_user_password').value = 'Alice draft password';
context.edit_candidate(2, 20, 'Bob', 'bob');

assert.equal(fields.get('edit_user_password').value, '');
assert.equal(fields.get('candidate_id').value, 2);
assert.equal(fields.get('event_id').value, 20);
assert.equal(fields.get('edit_name').value, 'Bob');
assert.equal(fields.get('edit_username').value, 'bob');
assert.equal(fields.get('edit_tabs').checked, true);
console.log('Selecting another candidate clears the password draft.');
