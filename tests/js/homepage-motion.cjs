const assert = require('node:assert/strict');
const fs = require('node:fs');
const path = require('node:path');
const vm = require('node:vm');

const file = path.join(__dirname, '../../public/js/homepage.js');
const source = fs.existsSync(file) ? fs.readFileSync(file, 'utf8') : '';

function initialize(reducedMotion, supported) {
    const observed = [];
    const revealed = [];
    const released = [];
    const elements = [1, 2].map(id => ({ id, classList: { add: name => revealed.push([id, name]) } }));
    let notify;
    class Observer {
        constructor(callback) { notify = callback; }
        observe(element) { observed.push(element); }
        unobserve(element) { released.push(element); }
    }
    vm.runInNewContext(source, {
        window: { matchMedia: () => ({ matches: reducedMotion }), ...(supported ? { IntersectionObserver: Observer } : {}) },
        IntersectionObserver: Observer,
        document: { querySelectorAll: () => elements },
    });
    return { observed, revealed, released, elements, notify };
}

const state = initialize(false, true);
assert.equal(state.observed.length, 2);
state.notify([{ target: state.elements[0], isIntersecting: false }, { target: state.elements[1], isIntersecting: true }]);
assert.deepEqual(state.revealed, [[2, 'is-visible']]);
assert.deepEqual(state.released, [state.elements[1]]);
assert.equal(initialize(true, true).observed.length, 0, 'Respect reduced motion.');
assert.equal(initialize(false, false).observed.length, 0, 'No observer support must remain usable.');
console.log('Homepage motion respects visibility, reduced motion and missing observer support.');
