### 2.7.0 - 2026-07-17

**Highlight:**

Introducing `has_one_relationship` option — configure it per side (`from`/`to`) to limit an item to a connect to only one item on the other side. Combined on both sides it creates a one-to-one relationship; on one side only, it creates one-to-many without duplicates.

Read more on [our blog](https://metabox.io/has-one-relationship) or [documentation](https://docs.metabox.io/extensions/mb-relationships/)

**Other changes:**

- Fix missing authorization check in `ajax_delete_file` for enhanced security
- Allow HTML in switch on/off and button group labels (e.g., Dashicons)
- Fix fatal error when post content (of mb-custom-post-type) contains invalid JSON
- Fix not displaying term meta in the Elementor taxonomy loop
- Fix deprecated notice in GroupField in Elementor

### 2.6.0 - 2026-07-07

**Highlights:**

This release introduces **Abilities**, enabling you to manage custom post types and taxonomies, as well as create, retrieve, update, and delete posts and terms.

See our [blog post](https://metabox.io/introducing-abilities/) for an overview or the [documentation](https://docs.metabox.io/abilities/) for usage details.

**Other changes:**

- Fix stale block lists when switching between block editor fields (#195)
- Fix conflict with `tax_query` in `each_connected` for relationships
- Fix unable to scroll in full screen mode for the `block_editor` field (#1689)
- Use `WP_Query`'s `search_columns` instead of custom `search_by_title` filter
- Fix autocomplete dropdown z-index in map/osm field inside MB Blocks
- Fix image fields not displaying in Divi blurb and image fields

### 2.5.2 - 2026-06-12

- Update style to match WordPress 7
- Improve admin menu width detection
- Add support for Divi 5
- Improve check for creating relationships table
- Fix showing block json path in admin columns when it's not enabled
- Fix prefix in field ids when importing
- Fix error when creating block with block.json and render_callback
- Fix missing tabs, sub-groups, default_state when importing

### 2.5.1 - 2026-05-13

- Show Cmd+S/Ctrl+S shortcut in the Save button in the builder
- Fix text limiter not working with WYSIWYG fields on settings pages
- Fix error in FieldKeys::all()
- Fix reorder posts not clearing object cache

### 2.5.0 - 2026-04-22

- New field type `link` that allows you to add a link with native WordPress experience (similar to ACF)
- Auto add child blocks for allowed blocks for the `block_editor` field
- Add allowed blocks callback that must return a list of blocks for the `block_editor` field
- Improve the UX of allowed block lists for the `block_editor` field
- Add missing `class`, `before`, `after` controls for the `custom_html` field
- Add time format support for the `datetime` field
- Remove all tabs settings when there are no fields
- Update the React app to React 18
- Fix detecting title and name changes
- Fix missing `sprintf` import for date time field preview
- Fix custom settings not available in the builder after import

### 2.4.5 - 2026-03-26

- Add keyboard shortcut `Ctrl+S` (or `Cmd+S` on Mac) to quick save when editting field groups, settings pages, relationships, post types, taxonomies and views.
- Block editor field: allow users to create/edit/select allowed block lists in the builder.
- Block editor field: add `toolbar_position` option for where to display editing toolbar for blocks, which accepts value `top` (default) or `contextual`.
- Fix saving an empty paragraph in the block editor field
- Fix extra empty clone saved when calling `set_post_data()` during validation
- Fix not showing tab icons after importing field groups
- Fix removed tabs but code generation still has code for tabs
- Fix geolocation not working with conditional logic

### 2.4.2 - 2026-03-09

**Improvements for the block editor field:**

- Add breadcrumbs
- Fix compatibility with Block Visibility plugin
- Fix not loading 3rd-party blocks
- Improve the CSS

**Other changes:**

- Fix save time format for the datetime field
- Fix icon field dropdown broken display when SVG contains double quotes
- Fix path traversal in `ajax_delete_file` for security
- Fix timestamp should not be set for the time picker field
- Add missing comma after capabilities in taxonomy PHP code output
- Fix warning when slug of custom post type/taxonomy is too long
- Fix updating WooCommerce products via REST API

### 2.4.1 - 2026-02-03

**Improvements for the block editor field:**

- Add block inspector sidebar
- Add structure panel to show the list view of blocks
- Add fullscreen mode
- Add `height` settings (default is `300px`) and allow resizing the editor

**Fixes for the block editor field:**

- Fix cannot upload images for the image block
- Fix blank site editor when using the block editor field
- Fix custom rich text formats not working
- Fix conflicts with `image_advanced` and `file_advanced` fields

**Other changes:**

- Fix cannot create new terms with required date/time fields

### 2.4.0 - 2026-01-15

**Highlights:**

Add new field type: `block_editor`. See more details on our [blog post](https://metabox.io/block-editor-field-type/) and [documentation](https://docs.metabox.io/fields/block-editor/).

**Other changes:**

- Rearrange settings for custom post type/taxonomy for a better UX
- Fix fatal error on custom post type/taxonomy list screens
- Fix cannot publish a field group if its status is future
- Clarify table name restriction

### 2.3.4 - 2025-12-04

- Fix text limiter not working if a field has prepend value
- Fix `sprintf` not imported, causing "Something is wrong" error

### 2.3.3 - 2025-11-24

- Fix the `use` statement with non-compound name has no effect

### 2.3.2 - 2025-11-24

- Add filters for reordering post types and taxonomies, allowing built-in or other post types/taxonomies can be reordered. See [docs for usage](https://docs.metabox.io/reorder-posts-terms/).
- Change icon for required/cloneable badge to avoid UX confusion
- Change tooltip for field label to top to always show the ID
- Hide date's `save_format` & `timestamp` settings when in a group to avoid confustion as they're not effective when inside groups
- Fix cannot add rules for Advanced location rules
- Fix cursor jumping when editing tooltip content
- Fix typing issue with Vietnamese in the field label in the field settings panel
- Fix incorrect location when importing field groups for settings pages
- Fix parsing JSON notation
- Fix deprecation message for `datetime` field

### 2.3.1 - 2025-11-14

- Update pt_BR translation
- Fix required text field preventing saving
- Fix no selected settings page causes the field group editor error
- WPML integration: fix error when filtering value for helper functions when no fields are found.

### 2.3.0 - 2025-11-05

**Highlights:**

Add toggle status column settings for quickly toggle Published/Draft status for custom post types.

**Other changes:**

- Fix bug with `meta_box_sanitize_cb` when importing custom post types from ACF
- WPML integration: filter helper functions to get the translated IDs for `post` field
- Fix cloning `post` field not clearing the value
- Remove IDs for custom HTML, divider, heading fields in the structure panel, and for custom HTML field in the preview area
- Fix parsing prefix for tabs
- Fix required URL & email fields preventing saving
- Fix cannot save field group again if saving it without title and ID
- Local JSON: fix custom table name not containing prefix
- Do not show default user orms & fields in the admin

### 2.2.0 - 2025-10-07

**Highlights:**

Add new feature for taxonomies: allow to reorder terms.

**Other changes:**

- Let users know when deleting a field, its data remains in the DB
- Revamp the API to register a new field type. Please follow [the docs](https://docs.metabox.io/creating-new-field-types/) for details.
- Add an option to disable dragging the pin on the map/osm fields
- Fix parsing/unparsing tabs for settings pages
- Fix empty block render code after reloading the editing page
- Fix cannot edit a field on Safari
- Set default menu position for settings page after all menus
- Fix menu icon (dashicons) not working for settings pages
- Remove "text_domain" from generated labels for post types
- Fix encoded unicode characters when importing post types

### 2.1.0 - 2025-09-16

- Ask before leaving editing field groups without saving changes
- Add an icon to the field label to show if field has `save_value` settings is off
- Use icon for required & cloneable settings for better accessibility
- Add disabled, readonly attributes to date, time fields
- Open field settings panel when add a new field
- Improve behavior when deleting a field by auto seting the next or previous field active
- Allow to change type for custom HTML, divider, heading, hidden fields
- Fix compatible with Gutenberg plugin
- Fix parsing std value for radio when it's 0
- Remove notice for feedback for the new UI

### Version 2.0.2 - 2025-08-21

- Allow to change field type
- Allow click to edit tab label
- Always show sub-fields for cloneable group with "Start with no inputs" enabled
- Set active field to new added field
- Prevent invalid characters when editing field ID
- Fix missing field prefix when auto creating custom tables
- Fix admin menu overlaying the app (caused by ASE)
- Fix cursor jump for ID, FileSize & block code

### 2.0.1 - 2025-08-18

Fix Open Street Maps field not showing (sometimes) with conditional logic

### 2.0.0 - 2025-08-05

Highlights:

This version introduces a rewritten the builder with a new UI/UX. [Learn more](https://metabox.io/mb-builder-5-0-0-rc2/) about the new UI.

### 1.4.0 - 2025-07-15

Highlights:

Rewrite the re-order feature, making works with hierarchical post types. Now you can drag and drop posts to re-order them and even setup parent/child relationships. To re-order posts, now you need to click the "Re-order" link in the header of the screen (near All | Published filters).

Other changes:

- Addmin filter for relationships: allow to remove selected choice
- Fix validation for blocks

### 1.3.3 - 2025-06-23

- Fix issue translating empty groups with WPML/Polylang
- Add German translation

## 1.3.2 - 2025-06-11

- Fix error for translating relationship texts
- Add German translations for some extensions

## 1.3.1 - 2025-05-21

- Fix datetime field returns null
- Fix single image field not working with Polylang Pro
- Fix `fields_translations` settings still available and grows rapidly event when Polylang is not active
- Fix reveal password not working
- Fix WPML integration with some languages

## 1.3.0 - 2025-05-08

Highlights:

Add integration for Polylang & improve the integration with WPML: allow translating settings pages/relationships/fields' labels and also value. For more details, please see this [blog post](https://metabox.io/wpml-polylang-integrations-improvements/).

Other changes:

- Add button to toggle password (#1630)
- Add gesture handling support for OSM field (#1631)
- Relationship admin filter: add localization for select2 and fix select2 width (#91)
- Datetime & select2: use user's locale instead of site's locale
- Fix conditional logic performance issue with new Builder's Local JSON feature, and improve performance for the block editor.
- Fix cloneable group issue with special characters with Elementor

### 1.2.0 - 2025-04-01

New feature: [local JSON](https://metabox.io/local-json/), which allows you to use JSON to define field groups, and eliminate querying database.

### 1.1.3 - 2025-03-14

Redesign the dashboard

### 1.1.2 - 2025-02-25

Fix not showing "Add new" button for cloneable fields.

### 1.1.1 - 2025-02-17

Fix language files

### 1.1.0 - 2025-02-04

- Add custom capabilities for taxonomies
- Add language packs
- Fix saving issues on Safari when creating post types
- Fix: output syntax error in Theme Code
- Improve style for text limiter with input group
- Fix output text limiter twice for wysiwyg

### 1.0.1 - 2025-01-10

Update license checker, ignore MB Builder as it's now free

### 1.0.0 - 2025-01-09

Initial release