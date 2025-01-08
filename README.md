# JWT Cookie Bridge for MiniOrange SSO

A WordPress plugin that securely stores JWT tokens from MiniOrange OAuth/OpenID in HTTP-only cookies for seamless SSO integration. Designed for WordPress sites requiring secure token management in multi-application environments.

## Features

### Core Functionality
- Secure cookie storage for JWT tokens with configurable settings
- Flexible SameSite policy support (Lax/Strict/None)
- Configurable cookie duration and attributes
- Enhanced token validation and security checks
- Translation-ready for international use

### Security
- HTTP-only cookie support with __Host- prefix
- Configurable SameSite policies
- Enhanced JWT validation
- Domain validation
- Token refresh handling
- Input validation and sanitization
- Output escaping
- Capability checking
- Nonce verification

### Debug & Monitoring
- Enhanced debug dashboard
- Token process tracking
- System status monitoring
- Comprehensive error logging
- Token validation status
- Process monitoring tools
- Log clearing functionality

## Requirements

### Software
- WordPress 5.0 or higher
- PHP 7.4 or higher
- MiniOrange OAuth/OpenID Enterprise edition

### Server
- Ability to set cookies with custom attributes
- Access to WordPress debug log (for debugging)
- Proper SSL/TLS configuration recommended

## Installation

1. Download from WordPress.org or upload to `/wp-content/plugins/`
2. Activate through WordPress admin interface
3. Configure through Settings → JWT Cookie Bridge
4. Set up cookie parameters according to your security requirements
5. Enable debug mode if needed for monitoring

## Configuration

### Core Settings
1. Navigate to Settings → JWT Cookie Bridge
2. Configure essential parameters:
   - Cookie name (prefixed with __Host-)
   - Cookie duration
   - SameSite policy
   - HTTP-only flag
   - Debug mode

### Security Settings
- Choose appropriate SameSite policy based on your domain structure
- Enable HTTP-only flag for enhanced security
- Configure cookie duration based on session requirements
- Set up proper cookie domain settings

### Debug Features
- Enable debug mode for monitoring
- Access debug dashboard for system status
- Monitor token processing
- Track cookie operations
- View and clear error logs

## Development

### Current Version
- Version: 1.0.7
- Requires WordPress: 5.0+
- Requires PHP: 7.4+

### Contributing
1. Fork the repository
2. Create a feature branch
3. Submit a pull request
4. Follow WordPress coding standards
5. Include proper documentation
6. Add unit tests when applicable

## Support

### Official Channels
- WordPress.org plugin support forum
- GitHub issue tracker
- Official documentation

### Getting Help
1. Check the FAQ in readme.txt
2. Review the debug dashboard
3. Submit support ticket if needed
4. Provide debug logs when reporting issues

## Security Considerations

### Cookie Security
- Uses __Host- prefix for enhanced security
- Configurable SameSite policy
- HTTP-only by default
- Domain validation
- Token validation
- Secure flag required

### Best Practices
- Regular updates
- Security audits
- Token validation
- Error monitoring
- Access control
- Input validation

## License

GPLv2 or later - see LICENSE file

## Credits

Developed by Allan Delmare for seamless integration between WordPress and external applications using MiniOrange OAuth/OpenID SSO.

## Changelog

### Version 1.0.7
- Enhanced JWT token validation and security
- Added __Host- prefix to cookies
- Improved error handling and logging
- Enhanced settings page UI and help text
- Added token refresh handling
- Improved debug dashboard functionality
- Added log clearing capability
- Added domain validation
- Enhanced security checks
- Improved input validation

### Version 1.0.6
- Initial release
- Basic functionality