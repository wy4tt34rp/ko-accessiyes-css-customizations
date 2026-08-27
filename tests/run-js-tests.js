'use strict';

const assert = require('assert');
const fs = require('fs');
const path = require('path');
const vm = require('vm');

const source = fs.readFileSync(path.join(__dirname, '..', 'assets', 'js', 'frontend.js'), 'utf8');

function execute(config, initialReady) {
	let ready = initialReady;
	let appended = null;
	let intervalCallback = null;
	let cleared = false;

	const root = {
		querySelector(selector) {
			return ready && selector === '.cya11y-menu-btn' ? {} : null;
		},
		getElementById() {
			return null;
		},
		appendChild(node) {
			appended = node;
		},
	};

	const context = {
		Date,
		document: {
			querySelector(selector) {
				return ready && selector === '#cya11y-container' ? { shadowRoot: root } : null;
			},
			createElement(tagName) {
				assert.strictEqual(tagName, 'style');
				return { id: '', textContent: '' };
			},
		},
		window: {
			KOAccessiYESCSS: config,
			setInterval(callback) {
				intervalCallback = callback;
				return 1;
			},
			clearInterval() {
				cleared = true;
			},
		},
	};

	vm.runInNewContext(source, context);

	return {
		getAppended: () => appended,
		getIntervalCallback: () => intervalCallback,
		wasCleared: () => cleared,
		setReady: (value) => {
			ready = value;
		},
	};
}

const immediate = execute({
	css: '.cya11y-menu-btn { border: 0 !important; }',
	hostSelector: '#cya11y-container',
	buttonSelector: '.cya11y-menu-btn',
	styleId: 'ko-accessiyes-css-customizations-rules',
	timeoutMs: 20000,
}, true);

assert.ok(immediate.getAppended(), 'Ready widget should receive a style element immediately.');
assert.strictEqual(immediate.getAppended().id, 'ko-accessiyes-css-customizations-rules');
assert.strictEqual(immediate.getAppended().textContent, '.cya11y-menu-btn { border: 0 !important; }');
assert.strictEqual(immediate.getIntervalCallback(), null, 'Ready widget should not start a retry timer.');

const delayed = execute({
	css: '.cya11y-menu-btn { box-shadow: none !important; }',
}, false);

assert.strictEqual(delayed.getAppended(), null);
assert.strictEqual(typeof delayed.getIntervalCallback(), 'function', 'Missing widget should start a retry timer.');
delayed.setReady(true);
delayed.getIntervalCallback()();
assert.ok(delayed.getAppended(), 'Retry should inject after the widget becomes available.');
assert.strictEqual(delayed.wasCleared(), true, 'Retry timer should stop after successful injection.');

const blank = execute({ css: '   ' }, true);
assert.strictEqual(blank.getAppended(), null, 'Blank CSS should not inject a style element.');
assert.strictEqual(blank.getIntervalCallback(), null, 'Blank CSS should not start a retry timer.');

console.log('All JavaScript behavior checks passed.');
