=== JWT Cookie Bridge for MiniOrange SSO ===
Contributors: allandelmare
Tags: sso, jwt, oauth, authentication, miniorange, keycloak, single sign-on, tokens, cookies
Requires at least: 5.0
Tested up to: 6.4
Requires PHP: 7.4
Stable tag: 1.0.6
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Securely store and manage JWT tokens from MiniOrange OAuth/OpenID in HTTP-only cookies for seamless Single Sign-On (SSO) integration.

== Description ==

JWT Cookie Bridge provides a secure and efficient way to handle JWT tokens from MiniOrange OAuth/OpenID Connect SSO solutions. Designed specifically for WordPress administrators who need to integrate their sites with external applications through SSO, this plugin ensures secure token storage and management.

Key Features:

* Secure HTTP-only cookie storage with configurable settings
* Flexible SameSite policy options (Lax/Strict/None)
* Comprehensive token validation and monitoring
* Advanced debug dashboard with process tracking
* Translation-ready for international use
* Detailed error logging and system status reporting
* Compatible with MiniOrange OAuth/OpenID Enterprise edition

Perfect for:
* WordPress sites requiring SSO integration
* Multi-application environments
* Enterprise authentication setups
* Keycloak integration scenarios
* Custom SSO implementations

Security Features:
* Secure cookie handling with HTTP-only option
* Configurable SameSite policies
* Input validation and sanitization
* Output escaping
* Capability checking
* Nonce verification
* Token validation checks

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
Tokens are stored in secure HTTP-only cookies with configurable SameSite policy for enhanced security.

= Can I customize the cookie settings? =
Yes, you can configure:
* Cookie name
* Duration
* SameSite policy
* HTTP-only flag
* Security options

= Is debug logging available? =
Yes, enabling debug mode provides access to:
* Token process tracking
* System status overview
* Error logging
* Token validation status

= What security measures are implemented? =
The plugin implements multiple security layers:
* Secure cookie handling with HTTP-only option
* Configurable SameSite policies
* Input validation and sanitization
* Output escaping
* Capability checking
* Nonce verification
* Token validation

= How do I report issues or request features? =
You can:
* Use the WordPress.org plugin support forum
* Submit issues on our GitHub repository
* Contact us through our support channels

== Screenshots ==

1. Main settings page
2. Debug dashboard overview
3. Token process monitoring
4. System status display

== Changelog ==

= 1.0.6 =
* Added comprehensive translation support
* Enhanced security validation for token handling
* Improved error handling and logging
* Added detailed system configuration overview
* Implemented token process monitoring
* Updated cookie handling for enhanced security
* Added support for additional SameSite policies
* Improved debug dashboard functionality
* Enhanced WordPress coding standards compliance
* Added security checks for all admin operations
* Implemented proper capability checking
* Enhanced input validation and sanitization

= 1.0.5 =
* Initial release with basic functionality
* Basic cookie management
* Simple debug features
* Core token handling

== Upgrade Notice ==

= 1.0.6 =
Important security and functionality improvements including enhanced token validation, improved error handling, and better cookie security. Upgrade recommended for all users.

== Privacy Notice ==

This plugin handles authentication tokens but does not store any personal user data. All tokens are stored client-side in cookies with configurable security settings.