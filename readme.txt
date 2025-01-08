=== JWT Cookie Bridge for MiniOrange SSO ===
Contributors: allandelmare
Tags: sso, jwt, oauth, authentication, miniorange
Requires at least: 5.0
Tested up to: 6.4
Stable tag: 1.0.5
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Securely handle JWT tokens for SSO integration between WordPress and external applications.

== Description ==
Securely handle JWT tokens from MiniOrange OAuth for SSO integration. Stores tokens in secure cookies with configurable settings.

Features:
* Secure HTTP-only cookie storage
* Configurable SameSite policy
* Enhanced debug dashboard with token process tracking
* Token validation monitoring
* System status reporting
* Customizable cookie settings

== Installation ==
1. Upload `jwt-cookie-bridge` directory to `/wp-content/plugins/`
2. Activate through WordPress admin interface
3. Configure through Settings → JWT Cookie Bridge

== Frequently Asked Questions ==
= How are tokens stored? =
Tokens are stored in secure HTTP-only cookies with configurable SameSite policy.

= What information does the debug dashboard show? =
The debug dashboard displays token process status, system configuration, and error logs when debug mode is enabled.

= What MiniOrange version is required? =
Enterprise or higher version of MiniOrange OAuth/OpenID plugin is required.

== Changelog ==
= 1.0.5 =
* Enhanced debug dashboard with token process tracking
* Added transient-based status monitoring
* Improved error logging and status reporting
* Added system configuration overview
* Optimized debug mode performance

= 1.0.4 =
* Generalized plugin for public release
* Improved error handling and logging
* Added comprehensive settings validation
* Enhanced security for cookie management