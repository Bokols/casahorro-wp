# Home Hero Rollback Guide

**Version:** 1.1.0  
**Last Updated:** November 4, 2025  
**Purpose:** Revert home hero to text-only layout (disable background images) while maintaining functionality

---

## Quick Reference

| Action | Method | Time Required |
|--------|--------|---------------|
| Disable backgrounds | Customizer (recommended) | 2 minutes |
| Disable backgrounds | Code comment | 1 minute |
| Re-enable backgrounds | Customizer upload | 3 minutes |
| Full rollback to v1.0.0 | Git + cache clear | 5 minutes |

---

## Method 1: Disable Background Images (Customizer)

**Use when:** You want to temporarily remove backgrounds without touching code.

### Steps to Disable

1. **Navigate to Customizer**
   - WordPress Admin → Appearance → Customize
   - Click "Home Hero" section

2. **Remove Background Images**
   - Desktop Background: Click "Remove" button
   - Tablet Background: Click "Remove" button  
   - Mobile Background: Click "Remove" button

3. **Publish Changes**
   - Click "Publish" button at top
   - Hard refresh front page (Ctrl+Shift+R)

**Result:** Hero displays with white background, text and CTA remain fully functional.

### Steps to Re-enable

1. **Upload New Images**
   - Desktop Background: Click "Select Image" → Upload/choose → Select
   - Tablet Background: Click "Select Image" → Upload/choose → Select
   - Mobile Background: Click "Select Image" → Upload/choose → Select

2. **Publish Changes**
   - Click "Publish" button
   - Hard refresh front page

**Recommended Image Specs:**
- Desktop: 1920×1080px, WebP format, <200KB
- Tablet: 1024×768px, WebP format, <150KB  
- Mobile: 768×1024px, WebP format, <100KB

---

## Method 2: Disable Background Images (Code)

**Use when:** You want code-level control or Customizer access is unavailable.

### Steps to Disable

1. **Edit home-hero.css**
   - File: `wp-content/themes/hello-elementor-child/assets/css/home-hero.css`
   - Find line ~19: `background-image: var(--hero-bg-mobile, none);`
   - Comment out background properties:

```css
.home-hero {
  display: grid;
  grid-template-columns: minmax(0, 0.58fr) 0.42fr;
  gap: clamp(16px, 3vw, 32px);
  /* ... other properties ... */
  
  /* BACKGROUNDS DISABLED - Uncomment to re-enable */
  /* background-image: var(--hero-bg-mobile, none); */
  /* background-repeat: no-repeat; */
  /* background-position: center; */
  /* background-size: cover; */
}

@media (min-width: 641px) and (max-width: 1023px) {
  .home-hero {
    /* ... other properties ... */
    
    /* BACKGROUNDS DISABLED - Uncomment to re-enable */
    /* background-image: 
      linear-gradient(...),
      var(--hero-bg-tablet, none);
    */
    /* background-position: center, top right; */
    /* background-size: 100% 100%, 40% auto; */
  }
}

@media (min-width: 1024px) {
  .home-hero {
    /* ... other properties ... */
    
    /* BACKGROUNDS DISABLED - Uncomment to re-enable */
    /* background-image: 
      linear-gradient(...),
      var(--hero-bg-desktop, none);
    */
    /* background-position: center, right center; */
    /* background-size: 100% 100%, contain; */
  }
}
```

2. **Clear Caches**
   - Autoptimize: Delete cache
   - Browser: Hard refresh (Ctrl+Shift+R)

**Result:** Hero displays white background, gradients removed, text/CTA functional.

### Steps to Re-enable

1. **Uncomment Background Properties**
   - Remove `/* */` around all commented background lines
   - Save file

2. **Clear Caches**
   - Autoptimize + browser hard refresh

---

## Method 3: Disable CSS Variable Printer

**Use when:** You want to stop outputting CSS variables but keep CSS file intact.

### Steps to Disable

1. **Edit functions.php**
   - File: `wp-content/themes/hello-elementor-child/functions.php`
   - Find line ~1337: `add_action( 'wp_head', 'cas_output_home_hero_css_vars', 10 );`
   - Comment out the hook:

```php
/**
 * Output home hero CSS variables to <head>
 * DISABLED: Uncomment line below to re-enable
 */
// add_action( 'wp_head', 'cas_output_home_hero_css_vars', 10 );
```

2. **Clear Caches**

**Result:** No `<style id="cas-home-hero-vars">` in page source, backgrounds fall back to `none`.

### Steps to Re-enable

1. **Uncomment Hook**
   - Remove `//` from `add_action` line
   - Save file

2. **Clear Caches**

---

## Method 4: Disable Home Hero CSS Entirely

**Use when:** You want to completely remove hero styling (emergency fallback).

### Steps to Disable

1. **Edit functions.php**
   - Find home hero enqueue block (lines ~63-73)
   - Comment out the entire conditional:

```php
/**
 * HOME HERO CSS - DISABLED
 * Uncomment block below to re-enable
 */
/*
if ( is_front_page() || is_page( 'inicio' ) ) {
    $home_hero_path = get_stylesheet_directory() . '/assets/css/home-hero.css';
    $home_hero_version = file_exists( $home_hero_path ) ? filemtime( $home_hero_path ) : $theme_version;
    
    wp_enqueue_style(
        'hello-elementor-child-home-hero',
        $base_uri . '/assets/css/home-hero.css',
        array( 'hello-elementor-child-tokens' ),
        $home_hero_version
    );
}
*/
```

2. **Clear Caches**

**Result:** Hero section reverts to Elementor default styles (may look broken).

**Warning:** This breaks the layout. Only use for emergency debugging.

### Steps to Re-enable

1. **Uncomment Enqueue Block**
   - Remove `/* */` wrapper
   - Save file

2. **Clear Caches**

---

## Method 5: Full Rollback to v1.0.0

**Use when:** You need to revert all hero changes (nuclear option).

### Prerequisites

- Git repository initialized
- v1.0.0 tagged in Git history
- Database backup (Customizer settings will be lost)

### Steps to Rollback

1. **Create Backup Branch**
```powershell
cd wp-content/themes/hello-elementor-child
git checkout -b backup-v1.1.0
git push origin backup-v1.1.0
```

2. **Revert to v1.0.0**
```powershell
git checkout main
git revert --no-commit HEAD~5..HEAD  # Adjust range as needed
# Or use hard reset (destructive):
git reset --hard v1.0.0
```

3. **Remove v1.1.0 Files**
```powershell
# If not in Git, manually delete:
rm docs/rollback.md
rm inc/asset-scanner.php
rm inc/image-optimizer-scanner.php
rm CHANGELOG.md
```

4. **Restore v1.0.0 Files**
```powershell
# Restore old home-hero.css (if needed)
git checkout v1.0.0 -- assets/css/home-hero.css

# Restore old functions.php sections
git checkout v1.0.0 -- wp-content/themes/hello-elementor-child/functions.php
```

5. **Update Version**
   - Edit `style.css` line 8: `Version: 1.0.0`

6. **Clear All Caches**
```powershell
# Via WP-CLI
wp cache flush
wp autoptimize clear

# Or via admin
# - Autoptimize → Delete Cache
# - Browser: Ctrl+Shift+F5
```

7. **Remove Customizer Settings (Optional)**
```powershell
wp theme mod remove cas_home_hero_bg_desktop
wp theme mod remove cas_home_hero_bg_tablet
wp theme mod remove cas_home_hero_bg_mobile
```

**Result:** Theme reverted to pre-hero-update state.

---

## Verification Checklist

After any rollback method, verify:

- [ ] Front page loads without errors
- [ ] Hero title displays correctly
- [ ] Hero subtitle displays correctly  
- [ ] Primary CTA button works (links to correct page)
- [ ] Secondary CTA hidden (if applicable)
- [ ] Layout responsive on mobile/tablet/desktop
- [ ] No console errors (F12 → Console tab)
- [ ] No PHP errors in debug.log (if WP_DEBUG enabled)

---

## Troubleshooting

### Background Images Not Disappearing

**Cause:** Browser cache or Autoptimize cache  
**Fix:**
1. Clear Autoptimize cache (Admin → Settings → Autoptimize → Delete Cache)
2. Hard refresh browser (Ctrl+Shift+R or Ctrl+F5)
3. Check in Incognito mode to confirm

### CTA Buttons Not Working After Rollback

**Cause:** JavaScript dependencies missing  
**Fix:**
1. Check `consent-events.js` is loading (View Page Source → search for "consent-events")
2. Check `assistant-glue.js` is loading (search for "assistant-glue")
3. Verify enqueue order in debug.log (if logger enabled)

### Layout Broken After CSS Disable

**Cause:** Elementor inline styles conflicting  
**Fix:**
1. Re-enable home-hero.css (see Method 4)
2. Or edit Elementor section settings:
   - Edit page with Elementor
   - Click hero section → Advanced → Custom CSS
   - Add: `display: grid; grid-template-columns: 1fr;`

### Customizer Settings Not Saving

**Cause:** Theme mod database permissions  
**Fix:**
1. Check database user has UPDATE permissions
2. Try via WP-CLI:
```powershell
wp theme mod set cas_home_hero_bg_desktop 123  # Replace 123 with attachment ID
```

---

## Re-enable Checklist

When re-enabling after rollback:

- [ ] Upload new background images to Media Library
- [ ] Set images in Customizer (Home Hero section)
- [ ] Uncomment CSS background properties (if using Method 2)
- [ ] Uncomment CSS var printer hook (if using Method 3)
- [ ] Uncomment enqueue block (if using Method 4)
- [ ] Clear Autoptimize cache
- [ ] Hard refresh browser
- [ ] Test on mobile/tablet/desktop
- [ ] Verify gradients display correctly (desktop/tablet)
- [ ] Check performance (PageSpeed Insights)

---

## Support Notes

**Common Questions:**

**Q: Will rollback affect other pages?**  
A: No. Home hero CSS only loads on front page (`is_front_page()` conditional).

**Q: Will Customizer settings persist after code rollback?**  
A: Yes. Theme mods stored in database. Only lost if manually deleted via WP-CLI.

**Q: Can I disable backgrounds but keep gradients?**  
A: Yes. In Customizer, remove all images. Gradients will show on white background (desktop/tablet only).

**Q: How do I test rollback without affecting live site?**  
A: Use Local by Flywheel clone, staging site, or create Git branch first.

---

## Contact

**Developer:** GitHub Copilot  
**Theme:** Hello Elementor Child v1.1.0  
**Documentation Version:** 1.0.0  
**Related Files:**
- `assets/css/home-hero.css` (hero styles)
- `functions.php` (enqueue + Customizer)
- `CHANGELOG.md` (version history)
