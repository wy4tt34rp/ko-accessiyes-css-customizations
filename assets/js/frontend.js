(function (window, document) {
	'use strict';

	var manager = window.KOAccessiYESManager;

	if (!manager || typeof manager !== 'object') {
		return;
	}

	var loadVendor = manager.loadVendor !== false;
	var scriptUrl = typeof manager.scriptUrl === 'string' ? manager.scriptUrl : '';
	var allowedScript = /^https:\/\/cdn-cookieyes\.com\/widgets\/accessibility\.js\?id=[A-Za-z0-9_-]{8,}$/i;

	if (loadVendor && !allowedScript.test(scriptUrl)) {
		return;
	}

	function integer(value, fallback) {
		var parsed = parseInt(value, 10);
		return Number.isFinite(parsed) ? parsed : fallback;
	}

	function currentLayout() {
		var width = integer(window.innerWidth, 1025);
		var mobileMax = integer(manager.breakpoints && manager.breakpoints.mobile_max, 767);
		var tabletMax = integer(manager.breakpoints && manager.breakpoints.tablet_max, 1024);
		var key = width <= mobileMax ? 'mobile' : (width <= tabletMax ? 'tablet' : 'desktop');
		var layouts = manager.layouts && typeof manager.layouts === 'object' ? manager.layouts : {};
		return layouts[key] && typeof layouts[key] === 'object' ? layouts[key] : {};
	}

	function normalizedCorner(value) {
		return ['top-left', 'top-right', 'bottom-left', 'bottom-right'].indexOf(value) !== -1 ? value : 'bottom-right';
	}

	function vendorMargins(layout) {
		var horizontal = Math.max(0, integer(layout.horizontal, 20));
		var vertical = Math.max(0, integer(layout.vertical, 20));

		return {
			top: vertical,
			bottom: vertical,
			left: horizontal,
			right: horizontal
		};
	}

	function cloneConfig(value) {
		try {
			return JSON.parse(JSON.stringify(value || {}));
		} catch (error) {
			return {};
		}
	}

	/*
	 * AccessiYES currently recognises only mobile and desktop. Seed both vendor
	 * slots from the active KO layout to prevent a positioning flash. The
	 * injected media queries remain authoritative and add the tablet range.
	 */
	if (loadVendor) {
		var layout = currentLayout();
		var corner = normalizedCorner(layout.corner);
		var widgetConfig = cloneConfig(manager.widgetConfig);

		widgetConfig.status = { mobile: true, desktop: true };
		widgetConfig.position = { mobile: corner, desktop: corner };
		widgetConfig.margins = {
			mobile: vendorMargins(layout),
			desktop: vendorMargins(layout)
		};

		window._cyA11yConfig = widgetConfig;

		if (manager.statementTemplates && typeof manager.statementTemplates === 'object') {
			window._cyA11yStatementTemplates = cloneConfig(manager.statementTemplates);
		}
	}

	var shadowCss = typeof manager.shadowCss === 'string' ? manager.shadowCss.trim() : '';
	var hostSelector = typeof manager.hostSelector === 'string' ? manager.hostSelector : '#cya11y-container';
	var buttonSelector = typeof manager.buttonSelector === 'string' ? manager.buttonSelector : '.cya11y-menu-btn';
	var styleId = typeof manager.styleId === 'string' ? manager.styleId : 'ko-accessiyes-css-customizations-rules';
	var timeoutMs = Math.max(1000, integer(manager.timeoutMs, 20000));
	var startedAt = Date.now();
	var retryTimer = null;

	function injectShadowStyles() {
		if (!shadowCss) {
			return true;
		}

		var host = document.querySelector(hostSelector);
		var root = host && host.shadowRoot;

		if (!root || !root.querySelector(buttonSelector)) {
			return false;
		}

		var style = typeof root.getElementById === 'function' ? root.getElementById(styleId) : null;
		if (!style) {
			style = document.createElement('style');
			style.id = styleId;
			root.appendChild(style);
		}

		if (style.textContent !== shadowCss) {
			style.textContent = shadowCss;
		}

		return true;
	}

	function stopRetrying() {
		if (retryTimer !== null) {
			window.clearInterval(retryTimer);
			retryTimer = null;
		}
	}

	if (!injectShadowStyles() && shadowCss) {
		retryTimer = window.setInterval(function () {
			if (injectShadowStyles() || Date.now() - startedAt >= timeoutMs) {
				stopRetrying();
			}
		}, 100);
	}

	if (!loadVendor) {
		return;
	}

	/* Do not create a second widget when the CDN script is already present. */
	var existingScript = document.querySelector('script[src^="https://cdn-cookieyes.com/widgets/accessibility.js"]');
	var existingWidget = document.querySelector(hostSelector);
	if (existingScript || existingWidget) {
		return;
	}

	var vendorScript = document.createElement('script');
	vendorScript.src = scriptUrl;
	vendorScript.async = true;
	vendorScript.setAttribute('data-ko-accessiyes-managed', '1');

	var firstScript = document.getElementsByTagName('script')[0];
	if (firstScript && firstScript.parentNode) {
		firstScript.parentNode.insertBefore(vendorScript, firstScript);
	} else if (document.head) {
		document.head.appendChild(vendorScript);
	} else if (document.body) {
		document.body.appendChild(vendorScript);
	}
}(window, document));
