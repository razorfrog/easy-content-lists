# Easy Content Lists

Easy Content Lists is a WordPress plugin that offers a set of shortcodes for efficiently listing pages, posts, taxonomies, and tags on your WordPress site. It provides users with an easy way to display content hierarchically and stylishly, offering various filtering and styling options.

- **WordPress.org:** https://wordpress.org/plugins/easy-content-lists/
- **Plugin page:** https://razorfrog.com/easy-content-lists/
- **Current release:** 1.0.3 · requires WordPress 5.2 and PHP 7.2 · tested up to WordPress 6.9
- **License:** GPLv2 or later

`readme.txt` is the WordPress.org plugin page (description, FAQ, changelog). For help, use the plugin's support forum on WordPress.org.

## Checks

Every push runs `.github/workflows/check.yml`:

- `php -l` on every PHP file under PHP 7.2 and 8.4
- PHP_CodeSniffer with `phpcs.xml.dist`: PHP 7.2+ compatibility (PHPCompatibilityWP) plus WordPress security sniffs (output escaping, nonces, input sanitizing, prepared SQL, deprecated functions)

Run the same locally with `phpcs` from the repo root once WPCS and PHPCompatibilityWP are installed.
