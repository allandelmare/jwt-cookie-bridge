# JWT Cookie Bridge for MiniOrange SSO

A WordPress plugin that securely stores JWT tokens from MiniOrange OAuth/OpenID in HTTP-only cookies for seamless SSO integration.

## Features

- Secure HTTP-only cookie storage for JWT tokens
- Configurable SameSite policy and cookie settings
- Debug dashboard for token inspection and management
- Compatible with MiniOrange OAuth/OpenID Enterprise edition
- Cookie domain and duration configuration

## Installation

1. Upload `jwt-cookie-bridge` directory to `/wp-content/plugins/`
2. Activate through WordPress admin interface
3. Configure through Settings → JWT Cookie Bridge

## Requirements

- WordPress 5.0+
- PHP 7.4+
- MiniOrange OAuth/OpenID Enterprise edition

## Configuration

Access plugin settings via WordPress admin:
1. Settings → JWT Cookie Bridge
2. Configure cookie name, duration, and security policies
3. Enable debug mode if needed

## Support

For issues and feature requests, please use the GitHub issue tracker.

## License

GPLv2 or later