# Breadcrumb Customization in Theme Academi

This guide separates what can be done **exclusively within the theme folder** (safe, portable) versus what requires changing Moodle core or other plugins.

## 1. Theme-Only Customizations (Safe)
These changes are made entirely within the `theme_academi` directory. They do **not** require touching `~/moodle41` or any other "system" files.

### A. Style & Appearance (SCSS)
**File:** `scss/includes.scss` or `scss/standard.scss`
Change colors, separators, or spacing using CSS.
*   *Example:* Changing the breadcrumb separator from `/` to `>`.
*   *Example:* Making the breadcrumbs sticky or changing the background color.

### B. Structural Overrides (Mustache)
**File:** `templates/core/navbar.mustache`
Moodle allows themes to "hijack" core templates. If you create this file in your theme, Moodle will use it instead of the system default.
*   **How:** Copy `~/moodle41/lib/templates/navbar.mustache` into your theme at `templates/core/navbar.mustache` and modify the HTML.
*   **Benefit:** You can add custom classes, icons, or wrap the breadcrumbs in new HTML containers without touching core code.

### C. Data Interception (Renderer Overrides)
**File:** `classes/output/core_renderer.php`
You can modify the breadcrumb data programmatically *after* Moodle generates it but *before* it is displayed.
*   **How:** Override the `navbar()` function in your theme's renderer.
*   **Use Case:** Automatically removing the "Home" link, renaming specific nodes, or injecting a "You are here:" prefix.

### D. Layout Placement
**File:** `layout/drawers.php` (and other layout files)
Change *where* the breadcrumbs appear on the screen.
*   **Example:** Moving the breadcrumbs from the header into the main content area or the footer.

---

## 2. Changes Requiring System/Plugin Access
You should only perform these if the theme-level overrides are insufficient.

### A. Core Navigation Nodes
If a Moodle core page (e.g., the site administration) is missing a specific breadcrumb node, that logic is defined in `lib/navigationlib.php`. 
*   *Theme Alternative:* Use a Renderer Override (1C) to manually add the missing node for that specific page type.

### B. Plugin Navigation
If a specific plugin (like a "Game" activity) doesn't show up correctly in the breadcrumbs, the fix usually belongs in that plugin's `lib.php` or `view.php`.
*   *Theme Alternative:* The theme can "search" for that plugin's nodes in the navbar object and modify them via the renderer.

## Summary: The "Theme-First" Rule
Always try to use a **Renderer Override** or **Mustache Override** within the `theme_academi` folder first. This keeps your Moodle core "clean" and makes updates much easier.
