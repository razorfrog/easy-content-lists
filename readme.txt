=== Easy Content Lists ===
Contributors: razorfrog
Tags: pages, posts, taxonomies, tags, lists
Requires at least: 5.2
Tested up to: 6.9
Requires PHP: 7.2
Stable tag: 1.0.4
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Shortcodes for easily listing all your pages, posts, taxonomies, and tags.

== Description ==
Easy Content Lists is a WordPress plugin that offers a set of shortcodes for efficiently listing pages, posts, taxonomies, and tags on your WordPress site. It provides users with an easy way to display content hierarchically and stylishly, offering various filtering and styling options.

== Installation ==
1. Upload the plugin files to the `/wp-content/plugins/easy-content-lists` directory, or install the plugin through the WordPress plugins screen directly.
2. Activate the plugin through the 'Plugins' screen in WordPress.

== Usage ==
Use the following shortcodes to display lists on your WordPress site:

- `[razorfrog_pages_list]` - For listing pages.
- `[razorfrog_posts_list]` - For listing posts.
- `[razorfrog_taxonomy_list]` - For listing taxonomy terms.
- `[razorfrog_tags_list]` - For displaying tags in a cloud format.

== Frequently Asked Questions ==

= What parameters does `[razorfrog_pages_list]` accept? =
- `orderby`: Order by 'title', 'date', etc. (default: 'title')
- `order`: 'ASC' or 'DESC' (default: 'ASC')
- `exclude`: Exclude specific page IDs (default: '')
- `child_of`: Display child pages of a specific page ID (default: 0)
- `col`: Column number for styling (default: 1)
- `error_message`: Custom error message (default: 'ERROR: Can\'t find pages.')

= What parameters does `[razorfrog_posts_list]` accept? =
- `post_type`: Specify the post type (default: 'post')
- `orderby`: Order by 'title', 'date', etc. (default: 'date')
- `order`: 'ASC' or 'DESC' (default: 'DESC')
- `exclude`: Exclude specific post IDs (default: '')
- `posts_per_page`: Number of posts to display (default: 100)
- `col`: Column number for styling (default: 1)
- `taxonomy`: Specify taxonomy to filter (default: '')
- `terms`: Specific terms to include/exclude (default: '')
- `meta_key`: Meta key for filtering (default: '')
- `meta_value`: Meta value for filtering (default: '')
- `error_message`: Custom error message (default: 'ERROR: Can\'t find posts.')

= What parameters does `[razorfrog_taxonomy_list]` accept? =
- `taxonomy`: Specify the taxonomy (default: 'category')
- `orderby`: Order by 'title', 'count', etc. (default: 'title')
- `order`: 'ASC' or 'DESC' (default: 'ASC')
- `col`: Column number for styling (default: 1)
- `count`: Show term count (default: 'true')
- `error_message`: Custom error message (default: 'ERROR: Can\'t find taxonomy.')

= What parameters does `[razorfrog_tags_list]` accept? =
- `query_type`: 'include' or 'exclude' specific tags (default: 'exclude')
- `tags_ids`: IDs of tags to include or exclude (default: '')
- `max_count`: Max number of tags to display (default: 100)
- `size_from`: Minimum font size for tags (default: 16)
- `size_to`: Maximum font size for tags (default: 36)
- `error_message`: Custom error message (default: 'ERROR: Can\'t find tags.')

== Changelog ==
= 1.0.4 =
- Accessibility fix: child pages and child terms are now nested inside their parent's list item, so the list HTML is valid and screen readers present subpages as part of their parent (WCAG 1.3.1)

= 1.0.3 =
- Security updates
- WP Core 6.9 compatibility update

= 1.0.2 =
- Excluded password protected pages and posts
- Updated for WP Core compatibility

= 1.0.1 =
- Added conditions to check if shortcodes already exist to avoid conflicts.
- Improved data sanitization and escaping.

= 1.0.0 =
- Initial release.
