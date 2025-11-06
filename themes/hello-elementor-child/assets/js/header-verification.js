/**
 * Header Verification Script
 * Run in browser console to validate header functionality
 * 
 * @package HelloElementorChild
 * @version 1.0.0
 */

(function() {
    'use strict';
    
    console.log('🔍 Header Verification Script - casAhorro');
    console.log('==========================================');
    
    // Test elements exist
    const header = document.getElementById('cas-header');
    const toggle = document.getElementById('cas-menu-toggle');
    const nav = document.getElementById('cas-nav-primary');
    const headerWrapper = document.querySelector('.header-wrapper');
    
    console.log('📋 Element Check:');
    console.log('Header:', header ? '✅' : '❌');
    console.log('Toggle:', toggle ? '✅' : '❌');
    console.log('Navigation:', nav ? '✅' : '❌');
    console.log('Wrapper:', headerWrapper ? '✅' : '❌');
    
    if (!header || !toggle || !nav) {
        console.error('❌ Missing required elements');
        return;
    }
    
    // Test viewport detection
    const viewport = window.innerWidth;
    const isMobile = viewport <= 1023;
    const isDesktop = viewport >= 1024;
    
    console.log('\n📐 Viewport Analysis:');
    console.log(`Width: ${viewport}px`);
    console.log(`Mobile Mode (≤1023px): ${isMobile ? '✅' : '❌'}`);
    console.log(`Desktop Mode (≥1024px): ${isDesktop ? '✅' : '❌'}`);
    
    // Test hamburger button specs
    if (isMobile) {
        const toggleStyles = window.getComputedStyle(toggle);
        const width = parseInt(toggleStyles.width);
        const height = parseInt(toggleStyles.height);
        
        console.log('\n🍔 Hamburger Button:');
        console.log(`Size: ${width}×${height}px (should be 44×44px)`);
        console.log(`Width correct: ${width === 44 ? '✅' : '❌'}`);
        console.log(`Height correct: ${height === 44 ? '✅' : '❌'}`);
        console.log(`Background: ${toggleStyles.backgroundColor}`);
        console.log(`Transparent: ${toggleStyles.backgroundColor === 'rgba(0, 0, 0, 0)' || toggleStyles.backgroundColor === 'transparent' ? '✅' : '❌'}`);
    }
    
    // Test menu items and order
    const menuItems = nav.querySelectorAll('.menu-primary a');
    const expectedOrder = ['Productos', 'Comparar', 'Sobre', 'Contacto'];
    
    console.log('\n📝 Menu Structure:');
    console.log(`Items found: ${menuItems.length}`);
    
    menuItems.forEach((item, index) => {
        const text = item.textContent.trim();
        const expected = expectedOrder[index];
        const isCorrect = text === expected;
        console.log(`${index + 1}. ${text} ${isCorrect ? '✅' : `❌ (expected: ${expected})`}`);
    });
    
    // Check for "Inicio" (should not exist)
    const hasInicio = Array.from(menuItems).some(item => 
        item.textContent.trim().toLowerCase().includes('inicio')
    );
    console.log(`No "Inicio": ${!hasInicio ? '✅' : '❌'}`);
    
    // Test colors and contrast
    if (menuItems.length > 0) {
        const firstLink = menuItems[0];
        const linkStyles = window.getComputedStyle(firstLink);
        
        console.log('\n🎨 Visual States:');
        console.log(`Default color: ${linkStyles.color}`);
        console.log(`Expected C1 primary: rgb(30, 41, 59) (#1E293B)`);
        console.log(`Color correct: ${linkStyles.color === 'rgb(30, 41, 59)' ? '✅' : '❌'}`);
    }
    
    // Test ARIA attributes
    console.log('\n♿ Accessibility:');
    const ariaExpanded = toggle.getAttribute('aria-expanded');
    const ariaControls = toggle.getAttribute('aria-controls');
    
    console.log(`aria-expanded: ${ariaExpanded} ${ariaExpanded !== null ? '✅' : '❌'}`);
    console.log(`aria-controls: ${ariaControls} ${ariaControls === 'cas-nav-primary' ? '✅' : '❌'}`);
    
    // Test consent system
    console.log('\n🛡️ Consent System:');
    if (typeof window.CasEvents !== 'undefined') {
        console.log('CasEvents API: ✅');
        console.log(`isAnalyticsAllowed function: ${typeof window.CasEvents.isAnalyticsAllowed === 'function' ? '✅' : '❌'}`);
        try {
            const consentStatus = window.CasEvents.isAnalyticsAllowed();
            console.log(`Consent status: ${consentStatus ? 'Granted ✅' : 'Not granted ❌'}`);
        } catch (e) {
            console.log('Consent check error:', e.message);
        }
    } else {
        console.log('CasEvents API: ❌ (not loaded)');
    }
    
    // Test sticky behavior
    console.log('\n📌 Sticky Behavior:');
    const isSticky = headerWrapper.classList.contains('is-scrolled');
    const scrollY = window.pageYOffset || document.documentElement.scrollTop;
    
    console.log(`Current scroll: ${scrollY}px`);
    console.log(`Sticky active: ${isSticky ? '✅' : '❌'}`);
    console.log(`Should be sticky: ${scrollY > 10 ? '✅' : '❌'}`);
    
    // Test height stability (CLS prevention)
    const containerStyles = window.getComputedStyle(header.querySelector('.cas-container'));
    const headerHeight = parseInt(containerStyles.height);
    
    console.log('\n📏 Layout Stability:');
    console.log(`Header height: ${headerHeight}px`);
    console.log(`Fixed height set: ${containerStyles.height !== 'auto' ? '✅' : '❌'}`);
    
    // Interactive test function
    window.testHeaderInteraction = function() {
        if (isMobile && toggle) {
            console.log('\n🖱️ Testing Mobile Interaction:');
            const wasExpanded = toggle.getAttribute('aria-expanded') === 'true';
            
            // Simulate click
            toggle.click();
            
            setTimeout(() => {
                const isExpanded = toggle.getAttribute('aria-expanded') === 'true';
                const toggleWorking = wasExpanded !== isExpanded;
                
                console.log(`Toggle working: ${toggleWorking ? '✅' : '❌'}`);
                console.log(`Panel ${isExpanded ? 'opened' : 'closed'}`);
                
                // Test focus
                const focusedElement = document.activeElement;
                console.log(`Focus management: ${focusedElement ? '✅' : '❌'}`);
                
            }, 100);
        } else {
            console.log('💻 Desktop mode - no mobile interaction to test');
        }
    };
    
    console.log('\n🚀 Run window.testHeaderInteraction() to test mobile toggle');
    console.log('✅ Verification complete!');
    
})();