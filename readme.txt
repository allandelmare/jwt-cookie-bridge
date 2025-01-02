=== Christendom SSO Token Handler ===
Contributors: adelmare
Tags: sso, jwt, oauth
Requires at least: 5.0
Tested up to: 6.4
Stable tag: 1.0.0
License: GPLv2
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Manages JWT tokens for SSO integration between WordPress (courses.christendom.edu) and Annunciate platform.

== Description ==
Handles secure token management for Single Sign-On between WordPress and Annunciate platform:
* Captures JWT tokens from MiniOrange OAuth
* Stores tokens securely as HTTP-only cookies
* Provides debug interface for administrators
* Facilitates seamless authentication between domains

== Installation ==
1. Upload plugin files to `/wp-content/plugins/christendom-sso/`
2. Activate plugin through WordPress admin
3. Configure MiniOrange OAuth plugin (Enterprise version required)

== Changelog ==
= 1.0.0 =
* Initial release
* JWT token handling
* Secure cookie management
* Debug dashboard