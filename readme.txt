=== Christendom SSO Token Handler ===
Contributors: adelmare
Tags: sso, jwt, oauth
Requires at least: 5.0
Tested up to: 6.4
Stable tag: 1.0.2
License: GPLv2
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Manages JWT tokens for SSO integration between WordPress (courses.christendom.edu) and Annunciate platform.

== Description ==
Handles secure token management for Single Sign-On between WordPress and Annunciate platform:
* Captures JWT tokens from MiniOrange OAuth
* Stores tokens securely as HTTP-only cookies
* Provides debug interface for administrators
* Facilitates seamless authentication between domains
* Configurable cookie security settings

== Installation ==
1. Upload plugin files to `/wp-content/plugins/christendom-sso/`
2. Activate plugin through WordPress admin
3. Configure MiniOrange OAuth plugin (Enterprise version required)
4. Configure cookie settings under Settings > Christendom SSO

== Changelog ==
= 1.0.2 =
* Added Settings Manager for cookie configuration
* Added admin settings page under Settings > Christendom SSO
* Configurable SameSite policy (Strict/Lax/None)
* Configurable HttpOnly flag
* Improved initialization sequence
* Enhanced namespace implementation

= 1.0.1 =
* Initial public release