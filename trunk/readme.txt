=== JWT Cookie Bridge for MiniOrange SSO ===
Contributors: allandelmare
Tags: sso, jwt, oauth, authentication, miniorange, keycloak, single sign-on, tokens, cookies
Requires at least: 5.0
Tested up to: 6.4
Requires PHP: 7.4
Stable tag: 1.0.9
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html
Securely store and manage JWT tokens from MiniOrange OAuth/OpenID in secure cookies for seamless Single Sign-On (SSO) integration.

== Description ==

JWT Cookie Bridge provides a secure and efficient way to handle JWT tokens from MiniOrange OAuth/OpenID Connect SSO solutions. Designed specifically for WordPress administrators who need to integrate their sites with external applications through SSO, this plugin ensures secure token storage and management.

Key Features:

* Comprehensive token validation and security checks
* Flexible SameSite policy options (Lax/Strict/None)
* Advanced debug dashboard with process tracking
* Translation-ready for international use
* Detailed error logging and system status reporting
* Domain validation and security checks
* Compatible with MiniOrange OAuth/OpenID Enterprise edition

Perfect for:
* WordPress sites requiring SSO integration
* Multi-application environments
* Enterprise authentication setups
* Keycloak integration scenarios
* Custom SSO implementations

Security Features:
* Configurable HTTP-only cookies (enabled by default)
* Configurable SameSite policies
* Enhanced token validation
* Domain validation
* Input validation and sanitization
* Output escaping
* Capability checking
* Nonce verification

== Installation ==

1. Upload the `jwt-cookie-bridge` directory to `/wp-content/plugins/`
2. Activate the plugin through the WordPress admin interface
3. Configure settings in Settings → JWT Cookie Bridge
4. If using with MiniOrange OAuth/OpenID plugin, ensure you have the Enterprise edition installed
5. Configure cookie settings according to your security requirements
6. Enable debug mode if needed for troubleshooting

== Frequently Asked Questions ==

= What is the minimum WordPress version required? =
WordPress 5.0 or higher is required.

= Is this compatible with MiniOrange OAuth/OpenID? =
Yes, this plugin requires the Enterprise or higher version of MiniOrange OAuth/OpenID plugin.

= How are the tokens stored? =
Tokens are stored in secure cookies with configurable settings including HTTP-only flag and SameSite policy for enhanced security.

= Can I customize the cookie settings? =
Yes, you can configure:
* Cookie name
* Duration
* SameSite policy
* HTTP-only setting (enabled by default)
* Security options

= Is debug logging available? =
Yes, enabling debug mode provides access to:
* Token process tracking
* System status overview
* Error logging
* Token validation status
* Log clearing functionality

= What security measures are implemented? =
The plugin implements multiple security layers:
* Configurable HTTP-only cookies
* Domain validation
* Token validation
* Configurable SameSite policies
* Input validation and sanitization
* Output escaping
* Capability checking
* Nonce verification

== Screenshots ==

1. Main settings page with cookie configuration options
2. Debug dashboard showing token status and system information
3. View of client side cookie

== Changelog ==
= 1.0.9 =

Removed WordPress.org deployment configuration
Changed HTTP-only cookie default to false for JavaScript accessibility
Updated documentation and deployment configuration

= 1.0.8 =
* Updated documentation for clarity and accuracy
* Enhanced plugin repository structure
* Added comprehensive screenshots and banners
* Updated deployment configuration
* Improved security documentation

= 1.0.7.2 =
* Fixed cookie domain handling for cross-domain compatibility
* Enhanced domain validation for subdomain support

= 1.0.7.1 =
* Fixed .yml packager

= 1.0.7 =
* Enhanced JWT token validation and security
* Improved error handling and logging
* Enhanced settings page UI and help text
* Added token refresh handling
* Improved debug dashboard functionality
* Added log clearing capability
* Added domain validation
* Enhanced security checks
* Improved input validation

= 1.0.6 =
* Initial release with basic functionality

== Privacy Notice ==

This plugin handles authentication tokens but does not store any personal user data. All tokens are stored client-side in cookies with configurable security settings.

== Upgrade Notice ==

= 1.0.8 =
Documentation update and repository structure enhancement. Recommended for all users.