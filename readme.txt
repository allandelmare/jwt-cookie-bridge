=== JWT Cookie Bridge for MiniOrange SSO ===
Contributors: allandelmare
Tags: sso, jwt, oauth, authentication, miniorange
Requires at least: 5.0
Tested up to: 6.4
Stable tag: 1.0.4
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Securely handle JWT tokens for SSO integration between WordPress and external applications.

== Description ==
Securely handle JWT tokens from MiniOrange OAuth for SSO integration. Stores tokens in secure HTTP-only cookies with configurable settings.

Features:
* Secure HTTP-only cookie storage
* Configurable SameSite policy
* Debug dashboard
* Token inspection tools
* Customizable cookie settings

== Installation ==
1. Upload `jwt-cookie-bridge` directory to `/wp-content/plugins/`
2. Activate through WordPress admin interface
3. Configure through Settings → JWT Cookie Bridge

== Frequently Asked Questions ==
= How are tokens stored? =
Tokens are stored in secure HTTP-only cookies with configurable SameSite policy.

= Is debug mode available? =
Yes, enable debug mode in Settings to access the debug dashboard.

= What MiniOrange version is required? =
Enterprise or higher version of MiniOrange OAuth/OpenID plugin is required.

== Changelog ==
= 1.0.4 =
* Generalized plugin for public release
* Improved error handling and logging
* Added comprehensive settings validation
* Enhanced security for cookie management

= 1.0.3 =
* Added configurable cookie name setting
* Changed default cookie name to 'mo_jwt'
* Updated settings interface
* Added automated build and release process

= 1.0.2 =
* Added Settings Manager
* Added admin settings page
* Configurable SameSite and HttpOnly settings
* Improved initialization

= 1.0.1 =
* Initial release