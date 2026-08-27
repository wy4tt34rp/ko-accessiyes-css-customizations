'use strict';

const assert = require('assert');
const fs = require('fs');
const path = require('path');
const vm = require('vm');

const source = fs.readFileSync(path.join(__dirname, '..', 'assets', 'js', 'frontend.js'), 'utf8');

function baseConfig() {
	return {
		scriptUrl: 'https://cdn-cookieyes.com/widgets/accessibility.js?id=8269f6cd-fd39-4411-b6c6-9d804bfd4584',
		hostSelector: '#cya11y-container',
		buttonSelector: '.cya11y-menu-btn',
		styleId: 'ko-accessiyes-css-customizations-rules',
		timeoutMs: 20000,
		breakpoints: { mobile_max: 767, tablet_max: 1024 },
		layouts: {
			mobile: { enabled: true, corner: 'bottom-left', horizontal: 11, vertical: 12 },
			tablet: { enabled: true, corner: 'top-left', horizontal: 21, vertical: 22 },
			desktop: { enabled: true, corner: 'top-right', horizontal: 31, vertical: 32 },
		},
		widgetConfig: {
			iconId: 'accessibility',
			iconSize: 64,
			position: { mobile: 'bottom-right', desktop: 'bottom-right' },
			margins: {
				mobile: { top: 20, bottom: 20, left: 20, right: 20 },
				desktop: { top: 20, bottom: 20, left: 20, right: 20 },
			},
		},
		shadowCss: '.cya11y-menu-btn { border: 0 !important; }',
		statementTemplates: {
			en: { template: '<h2>Independent template</h2>', conformance_options: {} },
		},
	};
}

function execute(config, options = {}) {
	let ready = options.ready !== false;
	let existingStyle = options.existingStyle || null;
	let intervalCallback = null;
	let cleared = false;
	let appendedStyle = null;
	let insertedVendorScript = null;
	let appendedVendorScript = null;

	const root = {
		querySelector(selector) {
			return ready && selector === '.cya11y-menu-btn' ? {} : null;
		},
		getElementById(id) {
			return existingStyle && existingStyle.id === id ? existingStyle : null;
		},
		appendChild(node) {
			appendedStyle = node;
			existingStyle = node;
		},
	};

	const firstScript = {
		parentNode: {
			insertBefore(node, reference) {
				assert.strictEqual(reference, firstScript);
				insertedVendorScript = node;
			},
		},
	};

	const document = {
		querySelector(selector) {
			if (selector === '#cya11y-container') {
				return ready ? { shadowRoot: root } : null;
			}
			if (selector === 'script[src^="https://cdn-cookieyes.com/widgets/accessibility.js"]') {
				return options.existingVendorScript ? {} : null;
			}
			return null;
		},
		createElement(tagName) {
			if (tagName === 'style') {
				return { id: '', textContent: '' };
			}
			if (tagName === 'script') {
				return {
					src: '',
					async: false,
					attributes: {},
					setAttribute(name, value) {
						this.attributes[name] = value;
					},
				};
			}
			throw new Error(`Unexpected element: ${tagName}`);
		},
		getElementsByTagName(tagName) {
			return tagName === 'script' && options.hasFirstScript !== false ? [firstScript] : [];
		},
		head: {
			appendChild(node) {
				appendedVendorScript = node;
			},
		},
		body: null,
	};

	const window = {
		KOAccessiYESManager: config,
		innerWidth: options.width || 900,
		setInterval(callback) {
			intervalCallback = callback;
			return 7;
		},
		clearInterval(id) {
			assert.strictEqual(id, 7);
			cleared = true;
		},
	};

	vm.runInNewContext(source, { Date, JSON, Math, Number, parseInt, window, document });

	return {
		window,
		root,
		getAppendedStyle: () => appendedStyle,
		getExistingStyle: () => existingStyle,
		getIntervalCallback: () => intervalCallback,
		wasCleared: () => cleared,
		getVendorScript: () => insertedVendorScript || appendedVendorScript,
		setReady(value) {
			ready = value;
		},
	};
}

const tablet = execute(baseConfig(), { ready: true, width: 900 });
assert.strictEqual(tablet.window._cyA11yConfig.position.mobile, 'top-left');
assert.strictEqual(tablet.window._cyA11yConfig.position.desktop, 'top-left');
assert.deepStrictEqual(
	JSON.parse(JSON.stringify(tablet.window._cyA11yConfig.margins.mobile)),
	{ top: 22, bottom: 22, left: 21, right: 21 },
	'Tablet layout was not seeded into both vendor device slots.'
);
assert.strictEqual(tablet.window._cyA11yConfig.iconId, 'accessibility', 'Managed vendor settings were not preserved.');
assert.strictEqual(tablet.window._cyA11yStatementTemplates.en.template, '<h2>Independent template</h2>');
assert.ok(tablet.getAppendedStyle(), 'Ready widget should receive a style element immediately.');
assert.strictEqual(tablet.getAppendedStyle().id, 'ko-accessiyes-css-customizations-rules');
assert.strictEqual(tablet.getAppendedStyle().textContent, '.cya11y-menu-btn { border: 0 !important; }');
assert.strictEqual(tablet.getIntervalCallback(), null, 'Ready widget should not start a retry timer.');
assert.strictEqual(tablet.getVendorScript(), null, 'An existing widget host should suppress a duplicate vendor loader.');

const mobile = execute(baseConfig(), { ready: true, width: 600 });
assert.strictEqual(mobile.window._cyA11yConfig.position.mobile, 'bottom-left');
assert.strictEqual(mobile.window._cyA11yConfig.margins.mobile.left, 11);
assert.strictEqual(mobile.window._cyA11yConfig.margins.mobile.top, 12);

const desktop = execute(baseConfig(), { ready: true, width: 1400 });
assert.strictEqual(desktop.window._cyA11yConfig.position.desktop, 'top-right');
assert.strictEqual(desktop.window._cyA11yConfig.margins.desktop.right, 31);
assert.strictEqual(desktop.window._cyA11yConfig.margins.desktop.top, 32);

const delayed = execute(baseConfig(), { ready: false });
assert.strictEqual(delayed.getAppendedStyle(), null);
assert.strictEqual(typeof delayed.getIntervalCallback(), 'function', 'Missing widget should start a retry timer.');
assert.ok(delayed.getVendorScript(), 'Vendor script was not created for a new integration.');
assert.strictEqual(delayed.getVendorScript().src, baseConfig().scriptUrl);
assert.strictEqual(delayed.getVendorScript().async, true);
assert.strictEqual(delayed.getVendorScript().attributes['data-ko-accessiyes-managed'], '1');
delayed.setReady(true);
delayed.getIntervalCallback()();
assert.ok(delayed.getAppendedStyle(), 'Retry should inject after the widget becomes available.');
assert.strictEqual(delayed.wasCleared(), true, 'Retry timer should stop after successful injection.');

const existingStyle = { id: 'ko-accessiyes-css-customizations-rules', textContent: 'old rules' };
const update = execute(baseConfig(), { ready: true, existingStyle });
assert.strictEqual(update.getAppendedStyle(), null, 'Existing style element should be updated instead of duplicated.');
assert.strictEqual(existingStyle.textContent, '.cya11y-menu-btn { border: 0 !important; }');

const duplicate = execute(baseConfig(), { ready: true, existingVendorScript: true });
assert.strictEqual(duplicate.getVendorScript(), null, 'Existing AccessiYES CDN script should suppress a duplicate loader.');
assert.ok(duplicate.getAppendedStyle(), 'Existing vendor script should still receive managed Shadow DOM styles.');

const styleOnlyConfig = baseConfig();
styleOnlyConfig.loadVendor = false;
styleOnlyConfig.scriptUrl = '';
const styleOnly = execute(styleOnlyConfig, { ready: true });
assert.strictEqual(styleOnly.getVendorScript(), null, 'Style-only mode created a vendor script.');
assert.strictEqual(styleOnly.window._cyA11yConfig, undefined, 'Style-only mode replaced the official plugin configuration.');
assert.ok(styleOnly.getAppendedStyle(), 'Style-only mode did not customize the existing Shadow DOM.');

const blankCssConfig = baseConfig();
blankCssConfig.shadowCss = '   ';
const blankCss = execute(blankCssConfig, { ready: false });
assert.strictEqual(blankCss.getAppendedStyle(), null, 'Blank CSS should not inject a style element.');
assert.strictEqual(blankCss.getIntervalCallback(), null, 'Blank CSS should not start a retry timer.');
assert.ok(blankCss.getVendorScript(), 'Blank custom CSS must not prevent the configured widget from loading.');

const invalidUrlConfig = baseConfig();
invalidUrlConfig.scriptUrl = 'https://evil.test/accessibility.js?id=12345678';
const invalidUrl = execute(invalidUrlConfig, { ready: true });
assert.strictEqual(invalidUrl.getVendorScript(), null, 'Unapproved script URL was loaded.');
assert.strictEqual(invalidUrl.getAppendedStyle(), null, 'Unapproved script URL still injected styles.');

const headFallback = execute(baseConfig(), { ready: false, hasFirstScript: false });
assert.ok(headFallback.getVendorScript(), 'Vendor script did not fall back to the document head.');

console.log('All JavaScript behavior checks passed.');
