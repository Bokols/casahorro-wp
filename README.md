# casAhorro — WP code

**WordPress custom theme and plugins for casAhorro e-commerce platform.**

## Repository Contents
**Tracked:** Child theme + mu-plugins (custom only)  
**Excluded:** WP core, 3P plugins, uploads, caches

## Structure
- `themes/hello-elementor-child/` - Custom child theme with design system
- `mu-plugins/cas-core/` - Custom must-use plugin for site-wide functionality

## Documentation
- **[SANITIZATION-CLEANUP-REPORT.md](SANITIZATION-CLEANUP-REPORT.md)** - Security audit and cleanup report
- **[themes/hello-elementor-child/LINTING.md](themes/hello-elementor-child/LINTING.md)** - CSS linting setup
- **[themes/hello-elementor-child/DESIGN-SYSTEM-COMPLIANCE.md](themes/hello-elementor-child/DESIGN-SYSTEM-COMPLIANCE.md)** - Design system compliance analysis

## Security Status
✅ **Last audit:** November 6, 2025  
✅ **Status:** Secure - All input sanitized, output escaped, 0 npm vulnerabilities  
✅ **PHP Security:** CSRF protection, XSS prevention, SQL injection prevention verified

## Development
```bash
cd themes/hello-elementor-child
npm install
npm run lint:css      # Run CSS linting
npm run lint:css:fix  # Auto-fix CSS issues
```
