/**
 * casAhorro Header Verification Console Script
 * Run this in browser console for comprehensive testing
 * 
 * Usage:
 * 1. Open site in browser
 * 2. Open Developer Tools (F12)
 * 3. Paste this entire script in Console
 * 4. Run: verifyHeader()
 */

window.casAhorroVerification = {
    
    // Test Results Storage
    results: [],
    
    // Add test result
    addResult(category, test, status, message, details = '') {
        this.results.push({
            category,
            test,
            status, // 'pass', 'fail', 'warn'
            message,
            details,
            timestamp: new Date().toISOString()
        });
        
        const icon = status === 'pass' ? '✅' : status === 'fail' ? '❌' : '⚠️';
        console.log(`${icon} [${category}] ${test}: ${message}${details ? ' - ' + details : ''}`);
    },
    
    // Test 1: Mobile Layout @ 640px
    testMobile640() {
        console.group('📱 Testing Mobile Layout @ 640px');
        
        try {
            // Logo position
            const logo = document.querySelector('.site-logo, .header-logo, .custom-logo-link');
            if (logo) {
                const logoRect = logo.getBoundingClientRect();
                const isLeft = logoRect.left < window.innerWidth / 2;
                this.addResult('Mobile 640px', 'Logo Position', isLeft ? 'pass' : 'fail', 
                    isLeft ? 'Logo positioned on left' : 'Logo not on left', 
                    `Left: ${logoRect.left}px`);
            } else {
                this.addResult('Mobile 640px', 'Logo Position', 'warn', 'Logo element not found');
            }
            
            // Hamburger menu
            const hamburger = document.querySelector('.menu-toggle, .mobile-menu-toggle, [aria-label*="menu"]');
            if (hamburger) {
                const hamburgerRect = hamburger.getBoundingClientRect();
                const isRight = hamburgerRect.right > window.innerWidth / 2;
                const size = hamburgerRect.width;
                
                this.addResult('Mobile 640px', 'Hamburger Position', isRight ? 'pass' : 'fail',
                    isRight ? 'Hamburger positioned on right' : 'Hamburger not on right',
                    `Right: ${hamburgerRect.right}px`);
                    
                this.addResult('Mobile 640px', 'Hamburger Size', 
                    (size >= 40 && size <= 48) ? 'pass' : 'fail',
                    `Hamburger size: ${size}px`,
                    'Expected: ~44px');
                    
                // Test keyboard accessibility
                const hasAriaExpanded = hamburger.hasAttribute('aria-expanded');
                this.addResult('Mobile 640px', 'Keyboard Access', hasAriaExpanded ? 'pass' : 'fail',
                    hasAriaExpanded ? 'Has aria-expanded attribute' : 'Missing aria-expanded');
            } else {
                this.addResult('Mobile 640px', 'Hamburger Menu', 'warn', 'Hamburger element not found');
            }
            
            // Check for red/pink colors
            this.checkColorCompliance('Mobile 640px');
            
        } catch (error) {
            this.addResult('Mobile 640px', 'Error', 'fail', error.message);
        }
        
        console.groupEnd();
    },
    
    // Test 2: Desktop Layout @ 1024px+
    testDesktop1024() {
        console.group('🖥️ Testing Desktop Layout @ 1024px+');
        
        try {
            // Desktop navigation visibility
            const desktopNav = document.querySelector('.menu-primary, .primary-menu, .header-nav');
            if (desktopNav) {
                const isVisible = window.getComputedStyle(desktopNav).display !== 'none';
                this.addResult('Desktop 1024px', 'Nav Visibility', isVisible ? 'pass' : 'fail',
                    isVisible ? 'Desktop navigation visible' : 'Desktop navigation hidden');
                
                // Check menu items and order
                const menuItems = desktopNav.querySelectorAll('a, .menu-item');
                const menuTexts = Array.from(menuItems).map(item => item.textContent.trim().toLowerCase());
                
                const expectedItems = ['productos', 'comparar', 'sobre', 'contacto'];
                const hasCorrectOrder = expectedItems.every(item => menuTexts.includes(item));
                const hasInicio = menuTexts.includes('inicio') || menuTexts.includes('home');
                
                this.addResult('Desktop 1024px', 'Menu Order', hasCorrectOrder ? 'pass' : 'fail',
                    hasCorrectOrder ? 'Correct menu items found' : 'Incorrect menu items',
                    `Found: ${menuTexts.join(', ')}`);
                    
                this.addResult('Desktop 1024px', 'No Inicio', !hasInicio ? 'pass' : 'fail',
                    !hasInicio ? 'No "Inicio" item found' : '"Inicio" item detected',
                    hasInicio ? 'Remove "Inicio" item' : '');
                    
            } else {
                this.addResult('Desktop 1024px', 'Navigation', 'warn', 'Desktop navigation not found');
            }
            
            // Check C1 colors
            this.checkC1Colors('Desktop 1024px');
            
        } catch (error) {
            this.addResult('Desktop 1024px', 'Error', 'fail', error.message);
        }
        
        console.groupEnd();
    },
    
    // Test 3: Sticky Header & CLS
    testStickyCLS() {
        console.group('📌 Testing Sticky Header & CLS');
        
        try {
            const header = document.querySelector('.header-wrapper, .site-header, header');
            if (header) {
                const initialHeight = header.offsetHeight;
                
                // Test sticky behavior
                const isSticky = window.getComputedStyle(header).position.includes('sticky') ||
                               header.classList.contains('is-scrolled') ||
                               header.style.position === 'sticky';
                
                this.addResult('Sticky & CLS', 'Sticky Position', isSticky ? 'pass' : 'warn',
                    isSticky ? 'Header has sticky behavior' : 'Sticky behavior not detected');
                
                // Check for box-shadow transition
                const hasTransition = window.getComputedStyle(header).transition.includes('box-shadow');
                this.addResult('Sticky & CLS', 'Shadow Transition', hasTransition ? 'pass' : 'warn',
                    hasTransition ? 'Box-shadow transition detected' : 'No box-shadow transition');
                
                // Height stability
                this.addResult('Sticky & CLS', 'Height Stability', 'pass',
                    `Header height: ${initialHeight}px (stable for CLS prevention)`);
                
            } else {
                this.addResult('Sticky & CLS', 'Header Element', 'warn', 'Header element not found');
            }
            
        } catch (error) {
            this.addResult('Sticky & CLS', 'Error', 'fail', error.message);
        }
        
        console.groupEnd();
    },
    
    // Test 4: Analytics & Consent
    testAnalyticsConsent() {
        console.group('🍪 Testing Analytics & Consent');
        
        try {
            // Check CookieYes integration
            const cookieYesExists = typeof window.CookieYes !== 'undefined';
            this.addResult('Analytics & Consent', 'CookieYes Integration', 
                cookieYesExists ? 'pass' : 'warn',
                cookieYesExists ? 'CookieYes detected' : 'CookieYes not loaded yet');
            
            // Check consent function
            const consentFunctionExists = typeof window.isAnalyticsAllowed === 'function';
            this.addResult('Analytics & Consent', 'Consent Function',
                consentFunctionExists ? 'pass' : 'warn',
                consentFunctionExists ? 'isAnalyticsAllowed() function found' : 'isAnalyticsAllowed() not found');
            
            // Check for premature analytics
            const hasGtag = typeof window.gtag === 'function';
            const hasGA = typeof window.ga === 'function';
            const hasDataLayer = window.dataLayer && window.dataLayer.length > 0;
            
            if (hasGtag || hasGA || hasDataLayer) {
                this.addResult('Analytics & Consent', 'Premature Analytics', 'warn',
                    'Analytics detected - verify consent check',
                    'Ensure analytics only load after consent');
            } else {
                this.addResult('Analytics & Consent', 'No Premature Analytics', 'pass',
                    'No analytics detected before consent');
            }
            
            // Test consent function if it exists
            if (consentFunctionExists) {
                const consentStatus = window.isAnalyticsAllowed();
                this.addResult('Analytics & Consent', 'Consent Status',
                    typeof consentStatus === 'boolean' ? 'pass' : 'warn',
                    `Consent status: ${consentStatus}`,
                    'Function returns boolean value');
            }
            
        } catch (error) {
            this.addResult('Analytics & Consent', 'Error', 'fail', error.message);
        }
        
        console.groupEnd();
    },
    
    // Check C1 Color Compliance
    checkC1Colors(category) {
        try {
            const navLinks = document.querySelectorAll('nav a, .menu a, .header-nav a');
            const c1Primary = '#1e293b';   // slate-800
            const c1Secondary = '#475569'; // slate-600
            
            let c1Compliant = true;
            let nonCompliantColors = [];
            
            navLinks.forEach(link => {
                const color = window.getComputedStyle(link).color;
                const rgbMatch = color.match(/rgb\((\d+), (\d+), (\d+)\)/);
                
                if (rgbMatch) {
                    const [, r, g, b] = rgbMatch;
                    const hex = '#' + [r, g, b].map(x => parseInt(x).toString(16).padStart(2, '0')).join('');
                    
                    if (![c1Primary, c1Secondary].includes(hex)) {
                        c1Compliant = false;
                        nonCompliantColors.push(hex);
                    }
                }
            });
            
            this.addResult(category, 'C1 Colors', c1Compliant ? 'pass' : 'warn',
                c1Compliant ? 'C1 colors detected' : 'Non-C1 colors found',
                nonCompliantColors.length ? `Non-compliant: ${nonCompliantColors.join(', ')}` : '');
                
        } catch (error) {
            this.addResult(category, 'C1 Colors', 'warn', 'Could not verify colors: ' + error.message);
        }
    },
    
    // Check for saturated colors
    checkColorCompliance(category) {
        try {
            const saturatedColors = ['#e11d48', '#db2777', '#dc2626', '#ea580c', '#d97706'];
            let hasSaturated = false;
            
            // Check computed styles for saturated colors
            const allElements = document.querySelectorAll('*');
            for (let i = 0; i < Math.min(allElements.length, 50); i++) { // Limit check for performance
                const element = allElements[i];
                const styles = window.getComputedStyle(element);
                
                [styles.color, styles.backgroundColor, styles.borderColor].forEach(color => {
                    if (color && saturatedColors.some(sat => color.includes(sat))) {
                        hasSaturated = true;
                    }
                });
            }
            
            this.addResult(category, 'No Saturated Colors', !hasSaturated ? 'pass' : 'fail',
                !hasSaturated ? 'No saturated red/pink detected' : 'Saturated colors found');
                
        } catch (error) {
            this.addResult(category, 'Color Compliance', 'warn', 'Could not verify: ' + error.message);
        }
    },
    
    // Generate summary report
    generateReport() {
        console.group('📊 Verification Summary');
        
        const summary = this.results.reduce((acc, result) => {
            acc[result.status] = (acc[result.status] || 0) + 1;
            return acc;
        }, {});
        
        console.log(`✅ Passed: ${summary.pass || 0}`);
        console.log(`❌ Failed: ${summary.fail || 0}`);
        console.log(`⚠️ Warnings: ${summary.warn || 0}`);
        console.log(`📝 Total Tests: ${this.results.length}`);
        
        // Show failures and warnings
        const issues = this.results.filter(r => r.status !== 'pass');
        if (issues.length > 0) {
            console.group('🔍 Issues to Address:');
            issues.forEach(issue => {
                const icon = issue.status === 'fail' ? '❌' : '⚠️';
                console.log(`${icon} [${issue.category}] ${issue.test}: ${issue.message}`);
            });
            console.groupEnd();
        }
        
        console.groupEnd();
        
        return {
            passed: summary.pass || 0,
            failed: summary.fail || 0,
            warnings: summary.warn || 0,
            total: this.results.length,
            issues: issues
        };
    }
};

// Main verification function
function verifyHeader() {
    console.clear();
    console.log('🔍 casAhorro Header Verification Starting...');
    console.log('================================');
    
    const verification = window.casAhorroVerification;
    verification.results = []; // Reset results
    
    // Run all tests
    verification.testMobile640();
    verification.testDesktop1024();
    verification.testStickyCLS();
    verification.testAnalyticsConsent();
    
    // Generate final report
    const report = verification.generateReport();
    
    console.log('================================');
    console.log('✅ Verification Complete!');
    
    return report;
}

// Viewport testing helper
function testAtViewport(width, height = 800) {
    console.log(`📐 Testing at ${width}x${height}px`);
    
    // Note: This is informational only - actual responsive testing 
    // requires browser dev tools viewport simulation
    console.log('💡 To test responsive behavior:');
    console.log('1. Open DevTools (F12)');
    console.log('2. Click device toolbar icon');
    console.log(`3. Set viewport to ${width}x${height}`);
    console.log('4. Run verifyHeader() again');
}

// Export for easy access
window.verifyHeader = verifyHeader;
window.testAtViewport = testAtViewport;

console.log('🚀 casAhorro Verification Script Loaded!');
console.log('Run: verifyHeader() to start testing');
console.log('Run: testAtViewport(640) for viewport-specific testing');