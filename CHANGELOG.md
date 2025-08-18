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