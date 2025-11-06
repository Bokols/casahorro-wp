# CSS Linting Setup

This theme uses Stylelint to enforce C1 design token usage and prevent color regressions.

## Quick Start

```bash
# Install dependencies
npm install

# Run CSS linting
npm run lint:css

# Auto-fix issues (where possible)
npm run lint:css:fix

# Run all linting
npm run lint
```

## Rules Enforced

### 1. **Color Token Enforcement** (Primary Rule)
All color-related properties MUST use CSS custom properties (variables).

**Enforced properties:**
- `color`
- `background-color`
- `border-color`
- `fill`
- `stroke`
- `background`
- `border`
- `border-*-color`
- `outline-color`
- `text-decoration-color`

**Allowed values:**
- CSS variables: `var(--color-*)`, `var(--accent-*)`, `var(--a11y-*)`
- Keywords: `transparent`, `currentColor`, `inherit`, `initial`, `unset`, `none`

**Exception:**
- Only `assets/tokens.css` is allowed to define raw color values (hex, rgb, rgba)

### 2. **No Hex Colors**
Hex colors are completely forbidden outside of `tokens.css`.

❌ **Wrong:**
```css
.button {
  background-color: #2563EB;
  color: #1E293B;
}
```

✅ **Correct:**
```css
.button {
  background-color: var(--color-surface-background);
  color: var(--color-text-primary);
}
```

### 3. **No RGB/HSL Functions**
RGB and HSL functions are forbidden outside of `tokens.css`.

❌ **Wrong:**
```css
.alert {
  background: rgb(37, 99, 235);
  border-color: rgba(30, 41, 59, 0.42);
}
```

✅ **Correct:**
```css
.alert {
  background: var(--color-accent-surface-mint);
  border-color: var(--color-border-default);
}
```

### 4. **Custom Property Pattern**
All custom properties must follow C1 token naming conventions.

**Allowed prefixes:**
- `--color-*`
- `--accent-*`
- `--a11y-*`
- `--spacing-*`
- `--font-*`
- `--radius-*`
- `--shadow-*`
- `--motion-*`
- `--container-*`
- `--surface-*`
- `--border-*`
- `--text-*`
- `--interactive-*`
- `--z-index-*`

✅ **Correct:**
```css
:root {
  --color-text-primary: #1E293B;
  --accent-surface-sage: #d9e8db;
  --spacing-4: 16px;
}
```

❌ **Wrong:**
```css
:root {
  --blue-primary: #2563EB;
  --margin-large: 32px;
}
```

## Files Covered

The linter scans:
- All CSS files in the child theme (`**/*.css`)
- All CSS files in mu-plugins (`../../mu-plugins/**/*.css`)

**Ignored:**
- Parent theme files
- Third-party plugins
- Minified files (`*.min.css`)
- Generated Elementor/WooCommerce styles

## Integration

### Pre-commit Hook (Optional)
Add to `.git/hooks/pre-commit`:
```bash
#!/bin/sh
npm run lint:css
```

### CI/CD Integration
```yaml
# .github/workflows/lint.yml
name: Lint
on: [push, pull_request]
jobs:
  lint:
    runs-on: ubuntu-latest
    steps:
      - uses: actions/checkout@v3
      - uses: actions/setup-node@v3
        with:
          node-version: 18
      - run: npm install
      - run: npm run lint:css
```

## Fixing Issues

### Auto-fix
Many issues can be automatically fixed:
```bash
npm run lint:css:fix
```

### Manual fixes
For color violations, replace hardcoded values with C1 tokens:

1. **Find the appropriate token** in `assets/tokens.css`
2. **Replace** the hardcoded color with `var(--token-name)`
3. **Verify** contrast ratios are maintained (WCAG AA = 4.5:1 minimum)

### Common replacements
| Hardcoded Value | C1 Token |
|----------------|----------|
| `#1E293B` | `var(--color-text-primary)` |
| `#475569` | `var(--color-text-secondary)` |
| `#64748B` | `var(--color-text-tertiary)` |
| `#E2E8F0` | `var(--color-border-default)` |
| `#F8FAFC` | `var(--color-surface-background-alt)` |
| `#FFFFFF` | `var(--color-surface-background)` |
| `#d9e8db` | `var(--color-accent-surface-sage)` |
| `#c9e5ea` | `var(--color-accent-surface-mint)` |
| `rgba(30,41,59,0.42)` | `var(--color-overlay)` |

## Troubleshooting

### "Unexpected color" error
You used a hardcoded color outside of `tokens.css`. Replace it with a CSS variable.

### "Invalid custom property pattern"
Your variable name doesn't follow C1 conventions. Use one of the allowed prefixes.

### False positives
If you have a legitimate edge case, you can disable the rule for that line:
```css
/* stylelint-disable-next-line scale-unlimited/declaration-strict-value */
.special-case {
  color: transparent;
}
```

**Note:** Use sparingly and document why the exception is needed.

## Benefits

✅ **Consistency:** All colors use C1 design tokens  
✅ **Maintainability:** Change tokens in one place, updates everywhere  
✅ **Accessibility:** Guaranteed WCAG AA contrast through token system  
✅ **Prevention:** Catches color regressions in development  
✅ **Documentation:** Self-documenting code through semantic token names  

## Related Files

- `.stylelintrc.json` - Main configuration
- `.stylelintignore` - Files to ignore
- `package.json` - NPM scripts
- `assets/tokens.css` - Token definitions (exception to all color rules)
- `tools/codemod-colors.ps1` - Script to bulk-fix color violations

## Support

For questions about C1 tokens or linting setup, see:
- `assets/tokens.css` - Complete token reference
- `tools/codemod-colors-report.md` - Recent color refactoring details
- `assets/css/color-guard.css` - Runtime color enforcement
