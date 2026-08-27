(function () {
	'use strict';

	var config = window.KOAccessiYESCSS || {};
	var css = typeof config.css === 'string' ? config.css.trim() : '';

	if (!css) {
		return;
	}

	var hostSelector = config.hostSelector || '#cya11y-container';
	var buttonSelector = config.buttonSelector || '.cya11y-menu-btn';
	var styleId = config.styleId || 'ko-accessiyes-css-customizations-rules';
	var timeoutMs = Number(config.timeoutMs) > 0 ? Number(config.timeoutMs) : 20000;
	var retryMs = 100;
	var startedAt = Date.now();
	var timer = null;

	function applyStyles() {
		var host = document.querySelector(hostSelector);
		var root = host && host.shadowRoot;
		var button = root && root.querySelector(buttonSelector);

		if (!root || !button) {
			return false;
		}

		var style = root.getElementById(styleId);

		if (!style) {
			style = document.createElement('style');
			style.id = styleId;
			root.appendChild(style);
		}

		if (style.textContent !== css) {
			style.textContent = css;
		}

		return true;
	}

	if (applyStyles()) {
		return;
	}

	timer = window.setInterval(function () {
		if (applyStyles() || Date.now() - startedAt >= timeoutMs) {
			window.clearInterval(timer);
		}
	}, retryMs);
})();
