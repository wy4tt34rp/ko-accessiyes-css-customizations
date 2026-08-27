# KO: AccessiYES CSS Customizations

## Overview

KO: AccessiYES CSS Customizations is a reusable, Vela-branded WordPress integration for the AccessiYES accessibility widget. Version 1.1.0 can load a site-specific AccessiYES script itself, rebuild the widget configuration from managed WordPress controls, and place responsive styling inside the widget's open Shadow DOM.

This is an independently developed KO/Vela integration. It is not affiliated with, sponsored by, or endorsed by AccessiYES. The name AccessiYES is used only to identify the compatible third-party service. No AccessiYES plugin code, JavaScript, CSS, statement templates, images, logos, or administration interface is included. Configuration property names are used solely as the technical interoperability contract for the vendor's CDN widget.

See [SOURCE-PROVENANCE.md](SOURCE-PROVENANCE.md) for the distribution's authorship and third-party boundary.

The plugin mirrors the current configuration surface of the official AccessiYES WordPress plugin—widget status, colour, icon, size, labels, language selection, keyboard shortcut, statement settings, profiles, content tools, colour tools, and navigation tools—then adds:

- A third, independently configurable tablet range.
- Custom mobile, tablet, and desktop breakpoints, visibility, corner, and margins.
- Separate inner-ring and outer-ring controls.
- An inner-icon width control.
- Optional advanced CSS injected directly into the widget's Shadow DOM.

## Site-Specific Installation Script

Open the [AccessiYES script generator](https://www.accessiyes.com/get-your-script/), generate a script for the site, and paste the complete snippet into **Settings > AccessiYES**.

The full snippet is stored in that site's WordPress options so the reusable public plugin contains no customer account ID. For safety, the plugin does not print or execute the pasted snippet. It extracts only an allowlisted URL in this format:

```text
https://cdn-cookieyes.com/widgets/accessibility.js?id=SITE_ID
```

The plugin then rebuilds `_cyA11yConfig` from the saved controls and loads that approved CDN URL asynchronously. When a new snippet is saved, the administrator can import supported configuration values from its JSON into the settings screen.

## Managed Settings

### Vendor-equivalent controls

- Master widget status plus mobile, tablet, and desktop visibility.
- Primary widget colour.
- Default, accessibility-person, wheelchair, and assist icons.
- 32px, 48px, and 64px launcher sizes.
- Accessible launcher label and menu heading.
- Fallback language and optional visitor language switcher.
- Alt+A or Option+A keyboard shortcut.
- Accessibility profiles.
- Font sizing, title highlighting, link highlighting, dyslexia-friendly font, font weight, letter spacing, line height, and text alignment.
- Dark contrast, light contrast, high contrast, high saturation, low saturation, and monochrome.
- Reading guide, pause animations, big cursor, page read, and mute sounds.
- Existing accessibility-statement URL.
- Generated statement details: company, contact email, website, target standard, conformance status, and display inside the widget.

The independently written KO generated-statement template is English and is populated only from the administrator's entries. It is not copied from or supplied by AccessiYES. Its wording must be reviewed before publication and is not legal advice.

### KO extensions

- Mobile maximum width, default `767px`.
- Tablet maximum width, default `1024px`.
- Independent launcher visibility, corner, horizontal margin, and vertical margin for mobile, tablet, and desktop.
- Inner ring on/off, width, and colour. This controls the launcher's border.
- Outer ring on/off, width, and colour. This controls the first solid `box-shadow` layer; a blank colour follows the primary widget colour.
- Inner person/icon width, with `0` retaining the vendor default.
- Advanced Shadow DOM CSS for narrowly scoped exceptions.

The subtle depth shadows remain when the solid outer ring is disabled. The separate keyboard focus outline is preserved and its offset is adjusted to the configured outer-ring width. A user-requested high-contrast or forced-colours mode may restore a visible boundary for legibility even when the normal rings are disabled.

## Why the Shadow DOM Bridge Is Required

AccessiYES creates a light-DOM host similar to:

```html
<div id="cya11y-container"></div>
```

The launcher and menu are rendered inside an open shadow root attached to that host. Browser Shadow DOM encapsulation prevents CSS from the theme, Customizer, Divi, page-level stylesheets, or ordinary `wp_add_inline_style()` output from matching internal selectors such as `.cya11y-menu-btn`.

This plugin waits for `#cya11y-container`, its open `shadowRoot`, and `.cya11y-menu-btn`. It then appends or updates one namespaced style element with the ID `ko-accessiyes-css-customizations-rules`. The wait stops after 20 seconds. Vendor files are never edited, so an AccessiYES update does not directly overwrite the managed styles.

The responsive rules also override the menu's left/right alignment. This keeps the menu and launcher on the configured side when a browser crosses the KO tablet breakpoint, even though the vendor script currently exposes only mobile and desktop configuration slots.

If AccessiYES changes its CDN host, host element, Shadow DOM mode, or internal selectors, this integration may need an update.

## Official Plugin Conflict Protection

Do not run this integration and the official `accessibility-widget` plugin as two widget loaders. When the official plugin is active, KO: AccessiYES CSS Customizations shows an admin warning, limits itself to applying the managed Shadow DOM styles to the existing widget, and deliberately does not load a second CDN widget or replace the official plugin's configuration. This preserves version 1.0.0 CSS during the transition.

For a safe transition:

1. Install and activate KO: AccessiYES CSS Customizations while the official plugin remains active.
2. Paste the generated script and configure all settings.
3. Save the settings.
4. Deactivate the official AccessiYES plugin.
5. Clear WordPress, Divi, host, and CDN caches.
6. Verify the launcher and menu on mobile, tablet, and desktop, including keyboard focus and the accessibility-statement link.

## Installation

1. Upload `ko-accessiyes-css-customizations-v1.1.0.zip` through **Plugins > Add New > Upload Plugin**, or copy the `ko-accessiyes-css-customizations` directory to `/wp-content/plugins/`.
2. Activate **KO: AccessiYES CSS Customizations**.
3. Open **Settings > AccessiYES**.
4. Paste the full site-specific generator script.
5. Review the imported values and configure the additional controls.
6. Save the settings.
7. Deactivate the official AccessiYES plugin before testing this plugin's loader.

New installations start with a blank script field. Updating from version 1.0.0 preserves existing advanced Shadow DOM CSS by migrating the former string option into the new settings array. Reactivation and normal plugin updates do not overwrite saved settings. Uninstalling the plugin removes its option.

## Advanced CSS

Enter CSS rules only, without `<style>` tags. The plugin removes style tags during sanitization and appends the remaining rules after the managed Shadow DOM rules, allowing a site-specific exception to take precedence.

Use advanced CSS sparingly. Managed controls should remain the first choice because they are validated and documented.

## Accessibility Scope

This plugin adds and styles a third-party visitor tool. It does not audit, remediate, certify, or guarantee the accessibility or legal compliance of a WordPress website. A widget is not a substitute for accessible source code, content practices, automated testing, knowledgeable manual testing, or legal advice.

## Development Notes

- Display name: `KO: AccessiYES CSS Customizations`
- Plugin slug: `ko-accessiyes-css-customizations`
- Option key: `ko_accessiyes_css_customizations`
- Script handle: `ko-accessiyes-css-customizations`
- Shadow style ID: `ko-accessiyes-css-customizations-rules`
- Text domain: `ko-accessiyes-css-customizations`
- Current version: `1.1.0`
- Vela contact: [ko@velaagency.com](mailto:ko@velaagency.com)

## License

GPL-2.0-or-later
