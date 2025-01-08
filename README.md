# JWT Cookie Bridge for MiniOrange SSO

A WordPress plugin that securely stores JWT tokens from MiniOrange OAuth/OpenID in HTTP-only cookies for seamless SSO integration. Designed for WordPress sites requiring secure token management in multi-application environments.

## Features

### Core Functionality
- Secure cookie storage for JWT tokens with configurable settings
- Flexible SameSite policy support (Lax/Strict/None)
- Configurable cookie duration and attributes
- Token validation and security checks
- Translation-ready for international use

### Security
- HTTP-only cookie support
- Configurable SameSite policies
- Input validation and sanitization
- Output escaping
- Capability checking
- Nonce verification
- Token validation checks

### Debug & Monitoring
- Enhanced debug dashboard
- Token process tracking
- System status monitoring
- Comprehensive error logging
- Token validation status
- Process monitoring tools

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

### Basic Settings
1. Navigate to Settings → JWT Cookie Bridge
2. Configure core settings:
   - Cookie name (default: mo_jwt)
   - Cookie duration
   - SameSite policy
   - HTTP-only flag
   - Debug mode

### Security Settings
- Choose appropriate SameSite policy based on your domain structure
- Enable HTTP-only flag for enhanced security
- Configure cookie duration based on your session requirements
- Set up proper cookie domain settings

### Debug Configuration
- Enable debug mode for monitoring
- Access debug dashboard for system status
- Monitor token processing
- Track cookie operations
- View error logs

## Debug Features

The debug dashboard provides:
- Token process status tracking
- System configuration overview
- Error log monitoring
- Token reset functionality
- Cookie status monitoring
- System health checks

## Development

### Current Version
- Version: 1.0.6
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
- Always use HTTPS
- Configure appropriate SameSite policy
- Enable HTTP-only when possible
- Set secure flag on cookies
- Implement proper token validation

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

### Version 1.0.6
- Added translation support
- Enhanced security validation
- Improved error handling
- Added system configuration overview
- Added token process monitoring
- Updated cookie handling
- Improved debug features
- Enhanced security checks
- Added capability verification
- Improved input validation

### Version 1.0.5
- Initial release
- Basic functionality
- Core features