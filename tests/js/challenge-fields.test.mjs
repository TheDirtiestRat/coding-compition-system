import assert from 'node:assert/strict';
import { readFileSync } from 'node:fs';
import vm from 'node:vm';

const source = readFileSync(new URL('../../resources/views/admin/challenges.blade.php', import.meta.url), 'utf8');
const entries = [];
const container = { children: entries, appendChild: entry => entries.push(entry) };
const document = {
    getElementById: () => container,
    createElement: () => {
        const fields = new Map();
        return {
            querySelector: selector => {
                if (!fields.has(selector)) fields.set(selector, { addEventListener() {} });
                return fields.get(selector);
            },
        };
    },
};
const markup = '</textarea><img src=x onerror=alert(1)> & \'quoted\'';
const context = vm.createContext({ document, testCaseIndex: 0, data: {
    input: markup, expected_output: markup, points: 0,
} });
const start = source.indexOf('        function addTestCaseEntry(');
const end = source.indexOf('        function copyCode()', start);
vm.runInContext(source.slice(start, end) + '\naddTestCaseEntry(data);', context);
assert.equal(entries[0].querySelector('[name$="[input]"]').value, markup);
assert.equal(entries[0].querySelector('[name$="[expected_output]"]').value, markup);
assert.equal(entries[0].querySelector('[name$="[points]"]').value, 0);
assert.ok(!entries[0].innerHTML.includes(markup));
assert.equal(context.testCaseIndex, 1);
console.log('Test case markup remains field data.');
