# Christendom SSO Token Handler

WordPress plugin for managing JWT tokens between courses.christendom.edu and Annunciate platform.

## Features
- Secure JWT token handling
- HTTP-only cookie storage
- Admin debug interface
- SSO integration with MiniOrange OAuth
- Configurable cookie security settings
- Configurable cookie name
- WordPress admin settings interface

## Requirements
- WordPress 5.0+
- MiniOrange OAuth Enterprise

## Version History
### 1.0.3
- Added configurable cookie name
- Changed default cookie name to 'sso_jwt'
- Updated settings interface

### 1.0.2
- Added Settings Manager for cookie configuration
- Added admin settings page under Settings > Christendom SSO
- Configurable SameSite policy (Strict/Lax/None)
- Configurable HttpOnly flag
- Improved initialization sequence
- Enhanced namespace implementation

### 1.0.1
- Initial public release