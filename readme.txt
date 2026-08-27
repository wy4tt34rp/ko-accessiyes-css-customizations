=== KO: AccessiYES CSS Customizations ===
Contributors: ko
Tags: accessiyes, accessibility widget, shadow dom, responsive, vela
Requires at least: 6.5
Tested up to: 7.0
Requires PHP: 7.4
Stable tag: 1.1.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Load a site-specific AccessiYES script, manage vendor-equivalent controls, and add responsive positioning and ring styling inside the Shadow DOM.

== Description ==

KO: AccessiYES CSS Customizations is a reusable, Vela-branded WordPress integration for the AccessiYES accessibility widget.

This is an independently developed KO/Vela integration. It is not affiliated with, sponsored by, or endorsed by AccessiYES. The AccessiYES name is used only to identify the compatible third-party service. No AccessiYES plugin code, JavaScript, CSS, statement templates, assets, or administration interface is included. Configuration property names are used only for technical interoperability with the vendor's CDN widget.

The settings screen accepts the complete site-specific script from the AccessiYES generator at https://www.accessiyes.com/get-your-script/. The snippet remains in that site's WordPress options. The plugin does not execute arbitrary pasted JavaScript: it extracts only the supported cdn-cookieyes.com accessibility.js URL and rebuilds the widget configuration from validated settings.

The plugin mirrors the current official widget controls:

* Widget status.
* Primary colour, icon shape, launcher size, accessible label, and menu heading.
* Fallback and visitor-selected languages.
* Keyboard shortcut.
* Accessibility profiles.
* Font, reading, colour, contrast, navigation, page-read, and mute-sounds modules.
* Existing accessibility-statement link.
* Generated-statement details and display inside the widget.

It also adds:

* Independent mobile, tablet, and desktop visibility.
* Configurable mobile and tablet breakpoints.
* Independent corner and margins for all three ranges.
* Separate inner-ring and outer-ring controls.
* An inner-icon width control.
* Optional advanced Shadow DOM CSS.

= Shadow DOM =

AccessiYES renders its launcher and menu inside an open Shadow DOM attached to `#cya11y-container`. Browser encapsulation prevents CSS in the theme, Customizer, Divi, page-level stylesheets, or the main document from matching internal elements such as `.cya11y-menu-btn`.

This plugin waits for the compatible host, open shadow root, and launcher, then creates or updates one namespaced style element inside the root. It stops after a bounded 20-second wait. AccessiYES files are not edited.

= Conflict protection =

When the official AccessiYES `accessibility-widget` plugin is active, this plugin shows an admin warning and limits itself to applying managed Shadow DOM styles to the existing widget. It does not load a second CDN widget or replace the official plugin's configuration. Configure this plugin first, then deactivate the official plugin before testing the managed script.

= Safe script handling =

The full generator snippet is retained only as an administrator-managed setting. The front end receives an allowlisted CDN URL plus the rebuilt configuration; it never receives or evaluates the arbitrary pasted snippet.

= Accessibility statement =

Administrators can link an existing statement or enter company, contact, website, target-standard, and conformance details for a generated statement inside the widget. The independently written KO generated-statement template is English and is not supplied by AccessiYES. Its wording is based entirely on administrator entries and must be reviewed before publication. It is not legal advice.

This plugin does not audit, remediate, certify, or guarantee website accessibility or legal compliance.

== Installation ==

1. Upload `ko-accessiyes-css-customizations-v1.1.0.zip` through Plugins > Add New > Upload Plugin.
2. Activate KO: AccessiYES CSS Customizations.
3. Open Settings > AccessiYES.
4. Generate a site-specific script at https://www.accessiyes.com/get-your-script/ and paste the complete snippet.
5. Review the imported values, configure the responsive and ring controls, and save.
6. Deactivate the official AccessiYES plugin before testing this plugin's loader.
7. Clear WordPress, Divi, host, and CDN caches.
8. Verify mobile, tablet, desktop, keyboard focus, and the statement link.

== Frequently Asked Questions ==

= Why does ordinary Divi or theme CSS not style the AccessiYES button? =

AccessiYES renders its interface inside a Shadow DOM. Shadow DOM style encapsulation prevents document-level CSS from selecting the widget's internal elements. This plugin places managed rules inside the compatible open shadow root after the widget is ready.

= Does this execute the script I paste? =

No. It extracts only a supported `https://cdn-cookieyes.com/widgets/accessibility.js?id=...` URL and supported JSON configuration. The front end loads that allowlisted URL with a configuration rebuilt from sanitized controls.

= Where do I get the installation script? =

Use the AccessiYES generator at https://www.accessiyes.com/get-your-script/. The settings screen includes the same link beside the script field.

= Can the official AccessiYES plugin remain active? =

It can remain active while this plugin is being configured. During that transition this plugin can continue applying its Shadow DOM styles, but it will not load its own widget until the official plugin is deactivated. This prevents duplicate launchers and duplicate global configuration.

= What are the default breakpoints? =

Mobile is 767px and below, tablet is 768px through 1024px, and desktop is 1025px and above. Both maximum values can be changed.

= What do the ring controls change? =

The inner ring controls the launcher border. The outer ring controls the first solid box-shadow layer. The subtle depth shadows remain when the outer ring is disabled, and the separate keyboard focus outline is preserved. High-contrast or forced-colours preferences may restore a visible boundary for legibility.

= Can I add a generated statement? =

Yes. The plugin exposes the company, email, website, target-standard, conformance-status, and in-widget display controls. The generated statement is English and must be reviewed before publication.

= What happens to version 1.0.0 CSS? =

The former CSS string is migrated into the Advanced Shadow DOM CSS field. Plugin updates and reactivation do not overwrite saved settings.

= What happens if AccessiYES changes its widget? =

The bridge expects the approved CDN host, `#cya11y-container`, an open shadow root, and `.cya11y-menu-btn`. If the vendor changes those contracts or its configuration schema, this plugin may need an update.

= Does this plugin make my site ADA compliant? =

No. A visitor widget and presentation changes are not substitutes for accessible source code, content practices, automated testing, knowledgeable manual testing, or legal advice.

== Changelog ==

= 1.1.0 =

* Added a site-specific AccessiYES script field with a direct generator link.
* Added allowlisted CDN extraction and validated configuration rebuilding.
* Added optional import of supported values from a newly pasted generator script.
* Added vendor-equivalent appearance, language, keyboard, statement, profile, content, colour, and navigation controls.
* Added generated-statement fields and an English in-widget template.
* Added configurable mobile, tablet, and desktop visibility, breakpoints, corner, and margins.
* Added independent inner-ring, outer-ring, and inner-icon sizing controls.
* Added official-plugin conflict protection to prevent duplicate widgets.
* Preserved the Shadow DOM bridge and Advanced CSS editor.
* Added migration of the version 1.0.0 CSS option.

= 1.0.0 =

* Added the Vela-branded AccessiYES CSS settings screen.
* Added a bounded Shadow DOM bridge for administrator-managed CSS.
* Added safe JSON encoding, style-tag sanitization, documentation, and tests.
