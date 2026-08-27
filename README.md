# KO: AccessiYES CSS Customizations

## Overview

KO: AccessiYES CSS Customizations is a Vela-branded WordPress plugin for managing CSS that targets the AccessiYES Accessibility Widget. It provides an editable CSS screen under **Settings > AccessiYES CSS** and places the saved rules inside the widget's Shadow DOM after the widget is ready.

Standard WordPress theme CSS, the Customizer, Divi Theme Options, page-level stylesheets, and ordinary `wp_add_inline_style()` output all live in the main document. AccessiYES renders its button and menu inside an open Shadow DOM attached to `#cya11y-container`. Shadow DOM encapsulation prevents selectors in the main document from matching internal elements such as `.cya11y-menu-btn`, even when the selectors and specificity are otherwise correct.

This plugin bridges that boundary without editing the AccessiYES plugin. Its front-end script waits for the host, open shadow root, and widget button, then appends one namespaced `<style>` element containing the administrator-managed CSS. AccessiYES updates therefore do not overwrite the saved CSS or this plugin's files.

## Key Features

- Exact display name: `KO: AccessiYES CSS Customizations`
- Vela-branded WordPress administration interface
- Site-wide CSS editor under the standard Settings menu
- Direct Settings link on the WordPress Plugins screen
- Informational AccessiYES activation status
- Shadow DOM-aware CSS injection
- No front-end script while the editor is blank
- A bounded 20-second wait for asynchronously rendered widgets
- Existing saved CSS preserved through plugin updates and reactivation
- Safe JSON encoding when saved CSS is passed to the bridge script
- No edits to AccessiYES, Divi, the active theme, or page content

## Requirements

- WordPress 6.5 or later
- PHP 7.4 or later
- AccessiYES Accessibility Widget (`accessibility-widget`)
- An open AccessiYES Shadow DOM hosted by `#cya11y-container`

## Installation

1. Upload `ko-accessiyes-css-customizations-v1.0.0.zip` through **Plugins > Add New > Upload Plugin**, or copy the `ko-accessiyes-css-customizations` directory to `/wp-content/plugins/`.
2. Activate **KO: AccessiYES CSS Customizations**.
3. Open **Settings > AccessiYES CSS**.
4. Enter the site-specific CSS without `<style>` tags and click **Save CSS**.
5. Clear site, Divi, host, and CDN caches before front-end verification.
6. Confirm the intended widget appearance and keyboard focus treatment on the live page.

The editor is blank on a new installation. This keeps site-specific presentation rules in the WordPress database rather than in the reusable public plugin source.

## How the Shadow DOM Bridge Works

The AccessiYES front end creates a light-DOM host similar to:

```html
<div id="cya11y-container"></div>
```

The widget attaches an open shadow root to that host and renders its button and menu inside the root. Browser style encapsulation means a document-level rule such as `.cya11y-menu-btn { ... }` cannot cross into the root.

When saved CSS exists, this plugin:

1. Enqueues a small footer script.
2. Passes the saved CSS through safely encoded JSON.
3. Checks for `#cya11y-container`, its `shadowRoot`, and `.cya11y-menu-btn` every 100 milliseconds.
4. Stops as soon as the compatible widget is ready or after 20 seconds.
5. Creates or updates a single style element with the ID `ko-accessiyes-css-customizations-rules` inside the shadow root.

If AccessiYES changes its host ID, closes its Shadow DOM, or renames its internal selectors, the bridge or saved CSS may need to be updated.

## CSS Editing

- Enter plain CSS only; do not include `<style>` tags.
- Clearing the editor and saving prevents the bridge script from loading.
- Saved CSS remains in the database during plugin updates and reactivation.
- Uninstalling the plugin removes its saved option.
- AccessiYES's own internal rules may use `!important`, so a narrowly targeted matching override may also require `!important`.
- Do not remove keyboard focus indicators merely to change the resting appearance of the launcher.
- For consistent CSS comments:
  - Entire blocks use `/***** Descriptor *****/`.
  - Sub-blocks use `/*** Descriptor ***/`.
  - The closing block repeats the opening descriptor.

## Example: Remove the Button Rings and Enlarge the Icon

The following removes the white inner border and the solid outer color ring, retains the two subtle depth shadows, enlarges the person icon, and leaves the widget's separate keyboard focus outline intact:

```css
/***** AccessiYES Accessibility Button *****/

/*** Remove Rings and Enlarge Icon ***/
.cya11y-menu-btn {
	border: 0 !important;
	padding: 0 !important;
	box-shadow:
		0 5px 15px #252c6126,
		0 2px 4px #5d649433 !important;
}

.cya11y-menu-btn .cya11y-btn-content {
	display: flex !important;
	align-items: center !important;
	justify-content: center !important;
	width: 100% !important;
	height: 100% !important;
	line-height: 0 !important;
}

.cya11y-menu-btn .cya11y-btn-content svg {
	display: block !important;
	width: 20px !important;
	min-width: 20px !important;
	max-width: 20px !important;
	height: auto !important;
	min-height: 0 !important;
	max-height: none !important;
}

/***** END AccessiYES Accessibility Button *****/
```

To remove every shadow rather than only the solid color ring, replace the `box-shadow` declaration with `box-shadow: none !important;`.

## Accessibility Scope

This plugin changes presentation rules inside the AccessiYES widget. It does not audit, remediate, certify, or guarantee the accessibility or legal compliance of a WordPress website. Automated widgets and CSS changes are not substitutes for source-level remediation and knowledgeable manual testing.

## Development Notes

- Display name: `KO: AccessiYES CSS Customizations`
- Plugin slug: `ko-accessiyes-css-customizations`
- Option key: `ko_accessiyes_css_customizations`
- Script handle: `ko-accessiyes-css-customizations`
- Shadow style ID: `ko-accessiyes-css-customizations-rules`
- Text domain: `ko-accessiyes-css-customizations`
- Current version: `1.0.0`
- Vela contact: [ko@velaagency.com](mailto:ko@velaagency.com)

## License

GPL-2.0-or-later
