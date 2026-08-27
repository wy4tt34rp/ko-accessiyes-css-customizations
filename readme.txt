=== KO: AccessiYES CSS Customizations ===
Contributors: ko
Tags: accessiyes, accessibility widget, shadow dom, css, vela
Requires at least: 6.5
Tested up to: 7.1
Requires PHP: 7.4
Requires Plugins: accessibility-widget
Stable tag: 1.0.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Manage AccessiYES CSS in a Vela-branded editor and inject the saved rules into the widget's otherwise isolated Shadow DOM.

== Description ==

KO: AccessiYES CSS Customizations provides a Vela-branded settings screen for site-wide AccessiYES CSS. It injects the saved rules directly into the accessibility widget's Shadow DOM, which ordinary theme, Customizer, Divi, page-builder, and document-level CSS cannot reach.

AccessiYES renders its launcher and menu inside an open Shadow DOM attached to `#cya11y-container`. Browser encapsulation prevents selectors in the main document from matching internal elements such as `.cya11y-menu-btn`, even when the selectors and specificity are otherwise correct.

This plugin leaves the AccessiYES files untouched. When saved CSS exists, a small front-end bridge waits for the compatible host, shadow root, and button, then appends one namespaced style element containing the administrator-managed CSS.

The plugin adds a CSS editor under Settings > AccessiYES CSS and a direct Settings link on the WordPress Plugins screen.

On the front end, the plugin:

* Loads no bridge script while the editor is blank.
* Safely passes saved CSS to a dedicated JavaScript file.
* Waits up to 20 seconds for asynchronously rendered AccessiYES markup.
* Targets the open Shadow DOM hosted by `#cya11y-container`.
* Requires `.cya11y-menu-btn` to exist before injecting the saved rules.
* Creates or updates one namespaced style element.
* Does not edit AccessiYES, Divi, the active theme, or page content.

The editor is blank on a new installation. Site-specific CSS is stored in the WordPress database, and plugin updates do not overwrite an existing saved CSS option.

This plugin controls presentation only. It does not audit, remediate, certify, or guarantee website accessibility or legal compliance.

== Installation ==

1. Upload `ko-accessiyes-css-customizations-v1.0.0.zip` through Plugins > Add New > Upload Plugin.
2. Activate KO: AccessiYES CSS Customizations.
3. Open Settings > AccessiYES CSS.
4. Add the site-specific CSS without style tags and click Save CSS.
5. Clear site, Divi, host, and CDN caches before front-end verification.
6. Confirm the intended widget appearance and keyboard focus treatment.

== Frequently Asked Questions ==

= Why does ordinary Divi or theme CSS not style the AccessiYES button? =

AccessiYES renders its interface inside a Shadow DOM. Shadow DOM style encapsulation prevents CSS in the main document from selecting the widget's internal elements. This plugin crosses that boundary by placing the saved CSS in a style element inside the open shadow root after the widget is ready.

= Does this edit the AccessiYES plugin? =

No. The bridge and saved CSS are owned by this plugin. AccessiYES files are not changed, so vendor updates do not directly overwrite the customization.

= What happens if AccessiYES changes its markup? =

The bridge expects the host `#cya11y-container`, an open shadow root, and the button `.cya11y-menu-btn`. If the vendor changes those contracts, this plugin or the saved CSS may need an update.

= Can I temporarily disable the custom CSS? =

Yes. Clear the CSS editor and save. The plugin remains active but loads no front-end bridge script.

= Can I paste style tags into the editor? =

No. Enter plain CSS only. Opening and closing style tags are removed during saving.

= Does this plugin make my site ADA compliant? =

No. It changes presentation rules inside one third-party widget. It does not replace source-level accessibility remediation, automated auditing, manual testing, or legal advice.

== Changelog ==

= 1.0.0 =

* Added the Vela-branded AccessiYES CSS settings screen.
* Added the Settings submenu and Plugins-screen Settings link.
* Added blank-by-default administrator-managed CSS storage.
* Added the bounded Shadow DOM bridge for asynchronously rendered AccessiYES markup.
* Added AccessiYES activation and saved-CSS status indicators.
* Added safe JSON encoding and style-tag sanitization.
* Documented Shadow DOM behavior, limitations, and a ring-removal/icon-sizing example.
