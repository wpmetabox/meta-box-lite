=== Meta Box Lite ===
Contributors: elightup, metabox, rilwis
Donate link: https://metabox.io/pricing/
Tags: custom fields, custom post types, post type, custom taxonomies, meta box
Requires at least: 6.7
Requires PHP: 7.4
Tested up to: 7.1
Stable tag: 2.12.1
License: GPLv2 or later

A powerful and performant tool to create custom post types and custom fields for WordPress.

== Description ==

**Meta Box Lite is an easy-to-use, powerful, and performant tool to create custom post types and custom fields for WordPress.**

The plugin provides an easy-to-use and WordPress-like interface to create post types and fields right inside the WordPress admin. The plugin has more than 40 different field types such as text, textarea, WYSIWYG editor, image/file, post, choice, date/time picker, taxonomy, user, oEmbed, and more.

Each custom field type has extensive internal options for unlimited content possibilities. Complete customization and control is just a few clicks away.

= Features =

- Create custom post types
- Create custom taxonomies
- Create custom fields for post, page or any post type
- Supports 40+ built-in field types for all your needs including text, textarea, WYSIWYG editor, image, file, post, select, checkbox, radio buttons, date/time picker, taxonomy, user, oembed and more to come
- Supports cloning fields for most field types including the WYSIWYG editor field
- Optimized for performance

== External Services ==

This plugin embeds a YouTube video in the plugin's settings pages for tutorial purposes. The video is displayed directly from YouTube but no data is collected or shared by the plugin.

== Privacy ==

No data is collected or shared by this plugin. However, YouTube may collect data in accordance with their [Privacy Policy](https://policies.google.com/privacy).

== Installation ==

1. Visit [Meta Box Lite](https://metabox.io/lite/)
1. Enter your email to download the plugin
1. Upload the plugin to your site
1. Activate it and you'll see **Meta Box** menu on the left

[Get started here](https://docs.metabox.io/introduction/).

== Changelog ==

= 2.12.1 - 2026-11-12 =

- Fix form being saved when a required checkbox or radio list is empty
- Fix "Get PHP Code" for post types with a custom capability type, so the code gives the same permissions as the plugin
- Fix "Get PHP Code" for field groups with a prefix, custom table or comment fields
- Fix a PHP warning when showing a fieldset text field that has no value
- Fix a PHP 8.1+ deprecation notice in the Pods migration
- Update bundled Meta Box, Meta Box Builder, MB Custom Post Type, ACF migration, Pods migration and Toolset migration extensions
- Require WordPress 6.7+ and PHP 7.4+
- Tested up to WordPress 7.1

= 2.12.0 - 2026-11-06 =

**Highlights:**

Add the "Remove post type slug from URL" option for post types, so posts can have URLs like `example.com/post-name/` instead of `example.com/post-type/post-name/`. For more details, please see [this blog post](https://metabox.io/remove-custom-post-type-slug/).

**Other changes:**

- Security: prevent stored XSS via slider, range and file input field values and map info window content
- Security: prevent changing the query of post, taxonomy and user fields via Ajax requests
- Security: prevent unauthenticated requests from triggering the MB Builder upgrade process
- Map field: use Places API (New) for address autocomplete, so new Google Cloud projects work. Old API keys still work
- User field: Ajax search no longer searches by email by default
- Show Meta Box abilities in the REST API
- Fix the wysiwyg field removing `<span>` tags without attributes
- Fix the post field with select advanced and multiple values in WooCommerce
- Fix field operations at the first position in abilities
- Fix the "Undefined array key" warning when importing fields
- Fix abilities not registered when the post type or taxonomy slug contains underscores
- Fix custom capability names for post types with accented characters
- Hide the REST API endpoints of MB REST API from the public REST API index (`/wp-json/`)
- Developers: changing `ajax_data.field.query_args` in JavaScript no longer has effect. Custom `ajax_action` handlers must not trust the `field` data sent in the request

= 1.0.1 - 2025-01-10 =

Update license checker, ignore Meta Box Builder as it's now free

= 1.0.0 - 2025-01-09 =

Initial release

== Upgrade Notice ==
