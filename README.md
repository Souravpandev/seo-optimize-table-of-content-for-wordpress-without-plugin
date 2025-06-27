# BNO Dynamic Table of Contents

Lightweight drop-in snippet that injects an interactive Table of Contents at render time.

Written for teachers, students, and bloggers who need clean navigation without any database overhead.

## Core Features

- **Builds the entire TOC on every page load** by parsing only `<h2>` and `<h3>` headings in the post body
- **Never calls `get_option`, `update_option`, transients or custom tables** so the `wp_options` table stays lean
- **Generates valid JSON-LD** using the Schema.org TableOfContents type which can help rich-result eligibility
- **Ships with a progressive-enhancement toggle script** that works even if JavaScript is disabled (TOC is still visible by default)
- **Adds a mobile-friendly sticky bar** under 768px that collapses and expands smoothly
- **All IDs are derived with `sanitize_title`** to prevent duplicate anchors and maintain WCAG compliance

## Performance and Optimization Notes

- **Single regular-expression pass** keeps CPU use minimal versus heavy DOM libraries
- **`the_content` filter priority set to 20** so it runs after short-code expansion but before caching plugins capture output
- **No autoloaded options**—nothing gets serialized into memory on each request which avoids object-cache thrashing
- **Inline CSS scoped inside `wp_head`** eliminates external style fetches; critical CSS only ~40 lines
- **Toggle handler attaches once via vanilla JS**, no jQuery dependency, < 1 KB after gzip

## How to Add the Code to WordPress

### Method 1: Child-theme functions.php

1. Open `/wp-content/themes/your-child-theme/functions.php`
2. Paste the entire PHP block at the bottom, save, and clear all caches

### Method 2: Must-use plugin (mu-plugin)

1. Create directory `/wp-content/mu-plugins` if it does not exist
2. Add file `bno-dynamic-toc.php` containing the snippet; WordPress autoloads it on every request
3. Ideal when switching themes or using a site-builder that overwrites theme files

### Method 3: Stand-alone plugin

1. `mkdir /wp-content/plugins/bno-dynamic-toc`
2. Inside it add `bno-dynamic-toc.php` with WordPress plugin header plus the code block
3. Activate through Dashboard → Plugins

## Configuration Ideas (all optional and done in code)

- **Change the pattern to include `<h4>`** by editing `/<h([2-3]).?/` → `/<h([2-4]).?/`
- **Rename the visible title** by replacing "On this page" inside the `toc-title-desktop` div
- **Update the breakpoint for sticky behavior** by modifying the `max-width` media query
- **Remove schema output** by commenting out the `$json` line if you do not want JSON-LD

## Usage Guidelines

- **Works automatically on any single post view**; pages or CPTs can be enabled by replacing `is_singular('post')` with `is_singular()`
- **Headings must be proper `<h2>` or `<h3>` elements** in the content editor—not wrapped in custom blocks that strip tags
- **If another plugin also adds anchor IDs**, the `sanitize_title` routine keeps them unique to prevent collisions
- **Tested with LiteSpeed Cache, WP Super Cache, and Cloudflare**; no special exclusions required because output is user-specific only by scroll position

## Troubleshooting Tips

- **TOC not showing** → ensure the post actually contains at least one `<h2>` tag
- **Toggle button unresponsive** → check console for JavaScript errors from other plugins then move the `wp_head` hook priority higher (e.g. 2)
- **Sticky bar overlaps admin bar** → add `top:32px;` inside the `.toc-container.sticky` rule when `user_can_manage_options()` is true

## License

MIT-style: feel free to fork, adapt, and redistribute with attribution where practical. 
