# Christendom SSO Token Handler

A WordPress plugin to handle JWT tokens for SSO integration between WordPress and external applications.

## Description
Manages secure storage of JWT tokens from MiniOrange OAuth/OpenID Connect for SSO integration, storing tokens in secure HTTP-only cookies with configurable settings.

## Installation
1. Upload `christendom-sso` directory to `/wp-content/plugins/`
2. Activate through WordPress admin interface
3. Configure through Settings → SSO Settings

## Development

### Prerequisites
- PHP 7.4+
- WordPress 5.0+
- MiniOrange OAuth/OpenID Enterprise

### Building
The plugin uses GitHub Actions for automated releases:

1. Tag a new version:
```bash
git tag -a v1.0.4 -m "Release version 1.0.4"
git push origin v1.0.4
```

2. GitHub Actions will automatically:
   - Create a new release
   - Build plugin zip file (christendom-sso-1.0.4.zip)
   - Attach zip to release

### Manual Build
To build manually:
```bash
mkdir -p build/christendom-sso
cp -r src build/christendom-sso/
cp christendom-sso.php build/christendom-sso/
cp readme.txt build/christendom-sso/
cp README.md build/christendom-sso/
cd build && zip -r ../christendom-sso.zip christendom-sso/
```

## License
GPLv2 or later