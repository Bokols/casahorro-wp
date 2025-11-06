<?php
/**
 * Template Name: C1 Color & Interaction Styleguide
 * Description: Visual reference for C1 color palette and standardized interaction patterns
 * 
 * @package HelloElementorChild
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}
?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo('charset'); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex,nofollow">
    <title>C1 Styleguide - Colors &amp; Interactions | <?php bloginfo('name'); ?></title>
    
    <?php wp_head(); ?>
    
    <style>
        /* Styleguide-specific styles */
        .styleguide-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 40px 20px;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
        }
        
        .styleguide-header {
            margin-bottom: 60px;
            padding-bottom: 30px;
            border-bottom: 2px solid var(--color-border-default, #E2E8F0);
        }
        
        .styleguide-header h1 {
            color: var(--color-text-primary, #1E293B);
            font-size: 2.5rem;
            margin: 0 0 12px 0;
        }
        
        .styleguide-header p {
            color: var(--color-text-secondary, #475569);
            font-size: 1.125rem;
            margin: 0;
        }
        
        .styleguide-section {
            margin-bottom: 60px;
        }
        
        .styleguide-section h2 {
            color: var(--color-text-primary, #1E293B);
            font-size: 1.875rem;
            margin: 0 0 24px 0;
            padding-bottom: 12px;
            border-bottom: 1px solid var(--color-border-medium, #CBD5E1);
        }
        
        .styleguide-section h3 {
            color: var(--color-text-secondary, #475569);
            font-size: 1.25rem;
            margin: 32px 0 16px 0;
        }
        
        .component-showcase {
            background: var(--color-surface-background, #FFFFFF);
            border: 1px solid var(--color-border-default, #E2E8F0);
            border-radius: 8px;
            padding: 32px;
            margin-bottom: 24px;
        }
        
        .component-row {
            display: flex;
            gap: 24px;
            flex-wrap: wrap;
            align-items: center;
            margin-bottom: 16px;
        }
        
        .component-row:last-child {
            margin-bottom: 0;
        }
        
        .state-label {
            width: 140px;
            color: var(--color-text-tertiary, #64748B);
            font-size: 0.875rem;
            font-weight: 500;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }
        
        .color-swatch {
            width: 120px;
            height: 80px;
            border-radius: 6px;
            border: 1px solid var(--color-border-default, #E2E8F0);
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            gap: 4px;
        }
        
        .color-swatch-label {
            font-size: 0.75rem;
            font-weight: 600;
            color: var(--color-text-primary, #1E293B);
        }
        
        .color-swatch-hex {
            font-size: 0.625rem;
            font-family: 'Courier New', monospace;
            color: var(--color-text-tertiary, #64748B);
        }
        
        .color-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(120px, 1fr));
            gap: 16px;
            margin-bottom: 32px;
        }
        
        .legend-section {
            background: var(--color-surface-background-alt, #F8FAFC);
            border: 1px solid var(--color-border-default, #E2E8F0);
            border-radius: 8px;
            padding: 24px;
            margin-bottom: 40px;
        }
        
        .legend-section h3 {
            margin-top: 0;
        }
        
        .reduced-motion-toggle {
            position: fixed;
            top: 20px;
            right: 20px;
            background: var(--color-surface-background, #FFFFFF);
            border: 2px solid var(--color-border-default, #E2E8F0);
            border-radius: 8px;
            padding: 16px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            z-index: 1000;
        }
        
        .reduced-motion-toggle label {
            display: flex;
            align-items: center;
            gap: 12px;
            cursor: pointer;
            font-size: 0.875rem;
            color: var(--color-text-primary, #1E293B);
        }
        
        .reduced-motion-toggle input[type="checkbox"] {
            width: 18px;
            height: 18px;
            cursor: pointer;
        }
        
        /* Table styles for table row showcase */
        .showcase-table {
            width: 100%;
            border-collapse: collapse;
        }
        
        .showcase-table th {
            background: var(--color-surface-background-alt, #F8FAFC);
            color: var(--color-text-secondary, #475569);
            font-weight: 600;
            text-align: left;
            padding: 12px 16px;
            border-bottom: 2px solid var(--color-border-medium, #CBD5E1);
        }
        
        .showcase-table td {
            padding: 12px 16px;
            border-bottom: 1px solid var(--color-border-default, #E2E8F0);
        }
        
        .showcase-table tbody tr {
            transition: background-color 0.15s ease;
        }
        
        .showcase-table tbody tr:hover {
            background: var(--color-surface-background-alt, #F8FAFC);
        }
        
        .showcase-table tbody tr:focus-within {
            outline: 3px solid currentColor;
            outline-offset: -3px;
        }
        
        /* Accordion styles */
        .accordion-item {
            border: 1px solid var(--color-border-default, #E2E8F0);
            border-radius: 6px;
            margin-bottom: 8px;
            overflow: hidden;
        }
        
        .accordion-header {
            width: 100%;
            background: var(--color-surface-background, #FFFFFF);
            color: var(--color-text-primary, #1E293B);
            border: none;
            padding: 16px 20px;
            text-align: left;
            font-size: 1rem;
            font-weight: 500;
            cursor: pointer;
            display: flex;
            justify-content: space-between;
            align-items: center;
            transition: background-color 0.15s ease;
        }
        
        .accordion-header:hover {
            text-decoration: underline;
        }
        
        .accordion-header:focus-visible {
            outline: 3px solid currentColor;
            outline-offset: -3px;
        }
        
        .accordion-content {
            padding: 0 20px;
            max-height: 0;
            overflow: hidden;
            transition: max-height 0.3s ease, padding 0.3s ease;
        }
        
        .accordion-item.active .accordion-content {
            max-height: 200px;
            padding: 16px 20px;
        }
        
        /* Card styles */
        .card-showcase {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 24px;
        }
        
        .card {
            background: var(--color-surface-background, #FFFFFF);
            border: 1px solid var(--color-border-default, #E2E8F0);
            border-radius: 8px;
            overflow: hidden;
            transition: box-shadow 0.15s ease, transform 0.15s ease;
        }
        
        .card:hover {
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.12);
            transform: translateY(-2px);
        }
        
        .card-image {
            width: 100%;
            height: 180px;
            background: var(--color-surface-background-alt, #F8FAFC);
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--color-text-tertiary, #64748B);
            font-size: 0.875rem;
        }
        
        .card-content {
            padding: 20px;
        }
        
        .card-title {
            margin: 0 0 8px 0;
            font-size: 1.25rem;
            color: var(--color-text-primary, #1E293B);
        }
        
        .card-description {
            margin: 0 0 16px 0;
            color: var(--color-text-secondary, #475569);
            font-size: 0.875rem;
            line-height: 1.5;
        }
        
        .card-link {
            color: var(--color-text-primary, #1E293B);
            text-decoration: underline;
            font-size: 0.875rem;
        }
        
        .card-link:hover {
            text-decoration: underline;
            color: var(--color-text-primary, #1E293B);
        }
        
        .card-cta {
            margin-top: 16px;
        }
        
        /* Utility for forcing hover state for screenshots */
        .force-hover-demo:hover {
            /* Component-specific hover states already defined above */
        }
        
        /* Reduced motion override */
        body.reduced-motion-override * {
            animation-duration: 0.01ms !important;
            animation-iteration-count: 1 !important;
            transition-duration: 0.01ms !important;
            scroll-behavior: auto !important;
        }
        
        .state-instruction {
            color: var(--color-text-tertiary, #64748B);
            font-size: 0.875rem;
            font-style: italic;
            margin-bottom: 16px;
        }
    </style>
</head>

<body <?php body_class('styleguide-page'); ?>>

<!-- Reduced Motion Toggle -->
<div class="reduced-motion-toggle">
    <label>
        <input type="checkbox" id="reduced-motion-toggle" aria-label="Activar movimiento reducido">
        <span>Movimiento Reducido</span>
    </label>
</div>

<div class="styleguide-container">
    
    <!-- Header -->
    <header class="styleguide-header">
        <h1>C1 Styleguide</h1>
        <p>Color palette and standardized interaction patterns for visual verification</p>
    </header>
    
    <!-- Color Palette Legend -->
    <section class="styleguide-section">
        <h2>C1 Color Palette</h2>
        
        <div class="legend-section">
            <h3>Primary Ink Colors</h3>
            <div class="color-grid">
                <div class="color-swatch" style="background-color: #1E293B;">
                    <span class="color-swatch-label" style="color: #FFFFFF;">Primary</span>
                    <span class="color-swatch-hex" style="color: #CBD5E1;">#1E293B</span>
                </div>
                <div class="color-swatch" style="background-color: #475569;">
                    <span class="color-swatch-label" style="color: #FFFFFF;">Secondary</span>
                    <span class="color-swatch-hex" style="color: #CBD5E1;">#475569</span>
                </div>
                <div class="color-swatch" style="background-color: #64748B;">
                    <span class="color-swatch-label" style="color: #FFFFFF;">Tertiary</span>
                    <span class="color-swatch-hex" style="color: #E2E8F0;">#64748B</span>
                </div>
            </div>
            
            <h3>Border Colors</h3>
            <div class="color-grid">
                <div class="color-swatch" style="background-color: #E2E8F0;">
                    <span class="color-swatch-label">Default</span>
                    <span class="color-swatch-hex">#E2E8F0</span>
                </div>
                <div class="color-swatch" style="background-color: #CBD5E1;">
                    <span class="color-swatch-label">Medium</span>
                    <span class="color-swatch-hex">#CBD5E1</span>
                </div>
                <div class="color-swatch" style="background-color: #94A3B8;">
                    <span class="color-swatch-label" style="color: #FFFFFF;">Dark</span>
                    <span class="color-swatch-hex" style="color: #E2E8F0;">#94A3B8</span>
                </div>
            </div>
            
            <h3>Surface Colors</h3>
            <div class="color-grid">
                <div class="color-swatch" style="background-color: #FFFFFF; border: 2px solid #CBD5E1;">
                    <span class="color-swatch-label">White</span>
                    <span class="color-swatch-hex">#FFFFFF</span>
                </div>
                <div class="color-swatch" style="background-color: #F8FAFC;">
                    <span class="color-swatch-label">Light</span>
                    <span class="color-swatch-hex">#F8FAFC</span>
                </div>
            </div>
            
            <h3>Pastel Accent Colors</h3>
            <div class="color-grid">
                <div class="color-swatch" style="background-color: #d9e8db;">
                    <span class="color-swatch-label">Sage</span>
                    <span class="color-swatch-hex">#d9e8db</span>
                </div>
                <div class="color-swatch" style="background-color: #c9e5ea;">
                    <span class="color-swatch-label">Mint</span>
                    <span class="color-swatch-hex">#c9e5ea</span>
                </div>
                <div class="color-swatch" style="background-color: #f2ddda;">
                    <span class="color-swatch-label">Coral</span>
                    <span class="color-swatch-hex">#f2ddda</span>
                </div>
                <div class="color-swatch" style="background-color: #cec7ee;">
                    <span class="color-swatch-label">Lavender</span>
                    <span class="color-swatch-hex">#cec7ee</span>
                </div>
                <div class="color-swatch" style="background-color: #f6e7c8;">
                    <span class="color-swatch-label">Cream</span>
                    <span class="color-swatch-hex">#f6e7c8</span>
                </div>
            </div>
        </div>
    </section>
    
    <!-- Links -->
    <section class="styleguide-section">
        <h2>Links</h2>
        <p class="state-instruction">Hover to see underline. Focus (Tab) to see outline ring.</p>
        
        <div class="component-showcase">
            <div class="component-row">
                <span class="state-label">Default</span>
                <a href="#" onclick="return false;">This is a standard link</a>
            </div>
            <div class="component-row">
                <span class="state-label">Hover</span>
                <a href="#" onclick="return false;">Hover over me to see underline (NO color change)</a>
            </div>
            <div class="component-row">
                <span class="state-label">Focus</span>
                <a href="#" onclick="return false;">Tab to me to see outline ring</a>
            </div>
            <div class="component-row">
                <span class="state-label">In Content</span>
                <p style="margin: 0;">
                    This is a paragraph with <a href="#" onclick="return false;">an inline link</a> that demonstrates 
                    the C1 link pattern: underline on hover, NO color change.
                </p>
            </div>
        </div>
    </section>
    
    <!-- Buttons -->
    <section class="styleguide-section">
        <h2>Buttons</h2>
        <p class="state-instruction">Hover to see −8% opacity. Focus (Tab) to see outline ring.</p>
        
        <h3>Primary Buttons</h3>
        <div class="component-showcase">
            <div class="component-row">
                <span class="state-label">Default</span>
                <button class="button button--primary" onclick="return false;">Primary Button</button>
            </div>
            <div class="component-row">
                <span class="state-label">Hover</span>
                <button class="button button--primary" onclick="return false;">Hover for opacity −8%</button>
            </div>
            <div class="component-row">
                <span class="state-label">Focus</span>
                <button class="button button--primary" onclick="return false;">Tab to see outline</button>
            </div>
            <div class="component-row">
                <span class="state-label">Disabled</span>
                <button class="button button--primary" disabled>Disabled Button</button>
            </div>
        </div>
        
        <h3>Secondary Buttons</h3>
        <div class="component-showcase">
            <div class="component-row">
                <span class="state-label">Default</span>
                <button class="button button--secondary" onclick="return false;">Secondary Button</button>
            </div>
            <div class="component-row">
                <span class="state-label">Hover</span>
                <button class="button button--secondary" onclick="return false;">Hover for opacity −8%</button>
            </div>
            <div class="component-row">
                <span class="state-label">Focus</span>
                <button class="button button--secondary" onclick="return false;">Tab to see outline</button>
            </div>
            <div class="component-row">
                <span class="state-label">Disabled</span>
                <button class="button button--secondary" disabled>Disabled Button</button>
            </div>
        </div>
    </section>
    
    <!-- Chips -->
    <section class="styleguide-section">
        <h2>Chips (Filter/Category Pills)</h2>
        <p class="state-instruction">Hover to see underline. Click to toggle selected state (sage pastel background).</p>
        
        <div class="component-showcase">
            <div class="component-row">
                <span class="state-label">Default</span>
                <button class="chip" onclick="this.classList.toggle('chip--selected'); return false;">
                    Unselected Chip
                </button>
            </div>
            <div class="component-row">
                <span class="state-label">Hover</span>
                <button class="chip" onclick="this.classList.toggle('chip--selected'); return false;">
                    Hover for underline
                </button>
            </div>
            <div class="component-row">
                <span class="state-label">Selected</span>
                <button class="chip chip--selected" onclick="this.classList.toggle('chip--selected'); return false;">
                    Selected Chip (Sage + Ink)
                </button>
            </div>
            <div class="component-row">
                <span class="state-label">Focus</span>
                <button class="chip" onclick="this.classList.toggle('chip--selected'); return false;">
                    Tab to see outline
                </button>
            </div>
        </div>
        
        <h3>Chip Group</h3>
        <div class="component-showcase">
            <div style="display: flex; gap: 12px; flex-wrap: wrap;">
                <button class="chip chip--selected" onclick="this.classList.toggle('chip--selected'); return false;">
                    All Products
                </button>
                <button class="chip" onclick="this.classList.toggle('chip--selected'); return false;">
                    Electronics
                </button>
                <button class="chip" onclick="this.classList.toggle('chip--selected'); return false;">
                    Furniture
                </button>
                <button class="chip" onclick="this.classList.toggle('chip--selected'); return false;">
                    Accessories
                </button>
                <button class="chip" onclick="this.classList.toggle('chip--selected'); return false;">
                    Home Decor
                </button>
            </div>
        </div>
    </section>
    
    <!-- Navigation Links -->
    <section class="styleguide-section">
        <h2>Navigation Links</h2>
        <p class="state-instruction">Hover to see underline (NO background fill). Focus to see outline.</p>
        
        <div class="component-showcase">
            <nav style="display: flex; gap: 32px; padding: 16px; background: var(--color-surface-background-alt, #F8FAFC); border-radius: 6px;">
                <a href="#" class="nav-link" onclick="return false;">Home</a>
                <a href="#" class="nav-link" onclick="return false;">Products</a>
                <a href="#" class="nav-link" onclick="return false;">About</a>
                <a href="#" class="nav-link" onclick="return false;">Contact</a>
            </nav>
        </div>
        
        <h3>Vertical Navigation</h3>
        <div class="component-showcase">
            <nav style="display: flex; flex-direction: column; gap: 8px; max-width: 200px;">
                <a href="#" class="nav-link" onclick="return false;" style="padding: 12px 16px; display: block;">Dashboard</a>
                <a href="#" class="nav-link" onclick="return false;" style="padding: 12px 16px; display: block;">Settings</a>
                <a href="#" class="nav-link" onclick="return false;" style="padding: 12px 16px; display: block;">Profile</a>
                <a href="#" class="nav-link" onclick="return false;" style="padding: 12px 16px; display: block;">Logout</a>
            </nav>
        </div>
    </section>
    
    <!-- Accordions -->
    <section class="styleguide-section">
        <h2>Accordion Headers</h2>
        <p class="state-instruction">Hover to see underline on header text. Click to expand/collapse. Focus (Tab) to see outline.</p>
        
        <div class="component-showcase">
            <div class="accordion-item">
                <button class="accordion-header" onclick="this.parentElement.classList.toggle('active'); return false;">
                    <span>Accordion Item 1</span>
                    <span>▼</span>
                </button>
                <div class="accordion-content">
                    <p>This is the content of accordion item 1. Notice the header uses underline on hover, not a background fill.</p>
                </div>
            </div>
            
            <div class="accordion-item">
                <button class="accordion-header" onclick="this.parentElement.classList.toggle('active'); return false;">
                    <span>Accordion Item 2</span>
                    <span>▼</span>
                </button>
                <div class="accordion-content">
                    <p>This is the content of accordion item 2. Demonstrates C1 interaction pattern compliance.</p>
                </div>
            </div>
            
            <div class="accordion-item active">
                <button class="accordion-header" onclick="this.parentElement.classList.toggle('active'); return false;">
                    <span>Accordion Item 3 (Expanded)</span>
                    <span>▼</span>
                </button>
                <div class="accordion-content">
                    <p>This accordion starts in the expanded state. Click the header to collapse.</p>
                </div>
            </div>
        </div>
    </section>
    
    <!-- Cards -->
    <section class="styleguide-section">
        <h2>Cards with Links &amp; CTAs</h2>
        <p class="state-instruction">Hover over card to see shadow lift and transform. Links use underline pattern. Buttons use opacity pattern.</p>
        
        <div class="card-showcase">
            <article class="card">
                <div class="card-image">
                    <span>Card Image</span>
                </div>
                <div class="card-content">
                    <h3 class="card-title">Card Title</h3>
                    <p class="card-description">
                        This is a card description. Cards use shadow and transform on hover (NO color change on card itself).
                    </p>
                    <a href="#" class="card-link" onclick="return false;">Read more</a>
                    <div class="card-cta">
                        <button class="button button--primary" onclick="return false;">Add to Cart</button>
                    </div>
                </div>
            </article>
            
            <article class="card">
                <div class="card-image">
                    <span>Card Image</span>
                </div>
                <div class="card-content">
                    <h3 class="card-title">Another Card</h3>
                    <p class="card-description">
                        Demonstrates consistent C1 patterns: link underline, button opacity, card shadow lift.
                    </p>
                    <a href="#" class="card-link" onclick="return false;">Learn more</a>
                    <div class="card-cta">
                        <button class="button button--secondary" onclick="return false;">View Details</button>
                    </div>
                </div>
            </article>
            
            <article class="card">
                <div class="card-image">
                    <span>Card Image</span>
                </div>
                <div class="card-content">
                    <h3 class="card-title">Third Card</h3>
                    <p class="card-description">
                        All interactive elements follow C1 standardized patterns for consistency.
                    </p>
                    <a href="#" class="card-link" onclick="return false;">Explore</a>
                    <div class="card-cta">
                        <button class="button button--primary" onclick="return false;">Get Started</button>
                    </div>
                </div>
            </article>
        </div>
    </section>
    
    <!-- Table Rows -->
    <section class="styleguide-section">
        <h2>Table Rows</h2>
        <p class="state-instruction">Hover over rows to see light background. Focus within row (Tab to link) to see outline.</p>
        
        <div class="component-showcase">
            <table class="showcase-table">
                <thead>
                    <tr>
                        <th>Product Name</th>
                        <th>Category</th>
                        <th>Price</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td><a href="#" onclick="return false;">Wireless Mouse</a></td>
                        <td>Electronics</td>
                        <td>$29.99</td>
                        <td><button class="button button--secondary" style="padding: 6px 12px; font-size: 0.875rem;" onclick="return false;">View</button></td>
                    </tr>
                    <tr>
                        <td><a href="#" onclick="return false;">Desk Lamp</a></td>
                        <td>Home Decor</td>
                        <td>$49.99</td>
                        <td><button class="button button--secondary" style="padding: 6px 12px; font-size: 0.875rem;" onclick="return false;">View</button></td>
                    </tr>
                    <tr>
                        <td><a href="#" onclick="return false;">Office Chair</a></td>
                        <td>Furniture</td>
                        <td>$199.99</td>
                        <td><button class="button button--secondary" style="padding: 6px 12px; font-size: 0.875rem;" onclick="return false;">View</button></td>
                    </tr>
                    <tr>
                        <td><a href="#" onclick="return false;">Notebook Set</a></td>
                        <td>Accessories</td>
                        <td>$12.99</td>
                        <td><button class="button button--secondary" style="padding: 6px 12px; font-size: 0.875rem;" onclick="return false;">View</button></td>
                    </tr>
                    <tr>
                        <td><a href="#" onclick="return false;">USB-C Cable</a></td>
                        <td>Electronics</td>
                        <td>$15.99</td>
                        <td><button class="button button--secondary" style="padding: 6px 12px; font-size: 0.875rem;" onclick="return false;">View</button></td>
                    </tr>
                </tbody>
            </table>
        </div>
    </section>
    
    <!-- Interaction Pattern Summary -->
    <section class="styleguide-section">
        <h2>C1 Interaction Pattern Summary</h2>
        
        <div class="legend-section">
            <h3>Standardized Rules</h3>
            
            <table style="width: 100%; border-collapse: collapse;">
                <thead>
                    <tr style="border-bottom: 2px solid var(--color-border-medium, #CBD5E1);">
                        <th style="text-align: left; padding: 12px 0; color: var(--color-text-secondary, #475569);">Component</th>
                        <th style="text-align: left; padding: 12px 0; color: var(--color-text-secondary, #475569);">Hover</th>
                        <th style="text-align: left; padding: 12px 0; color: var(--color-text-secondary, #475569);">Focus</th>
                        <th style="text-align: left; padding: 12px 0; color: var(--color-text-secondary, #475569);">Selected/Active</th>
                    </tr>
                </thead>
                <tbody>
                    <tr style="border-bottom: 1px solid var(--color-border-default, #E2E8F0);">
                        <td style="padding: 12px 0; color: var(--color-text-primary, #1E293B); font-weight: 500;">Links</td>
                        <td style="padding: 12px 0; color: var(--color-text-secondary, #475569);">Underline (NO color change)</td>
                        <td style="padding: 12px 0; color: var(--color-text-secondary, #475569);">3px outline</td>
                        <td style="padding: 12px 0; color: var(--color-text-tertiary, #64748B);">—</td>
                    </tr>
                    <tr style="border-bottom: 1px solid var(--color-border-default, #E2E8F0);">
                        <td style="padding: 12px 0; color: var(--color-text-primary, #1E293B); font-weight: 500;">Buttons</td>
                        <td style="padding: 12px 0; color: var(--color-text-secondary, #475569);">Opacity −8% (NO hue shift)</td>
                        <td style="padding: 12px 0; color: var(--color-text-secondary, #475569);">3px outline</td>
                        <td style="padding: 12px 0; color: var(--color-text-tertiary, #64748B);">—</td>
                    </tr>
                    <tr style="border-bottom: 1px solid var(--color-border-default, #E2E8F0);">
                        <td style="padding: 12px 0; color: var(--color-text-primary, #1E293B); font-weight: 500;">Chips</td>
                        <td style="padding: 12px 0; color: var(--color-text-secondary, #475569);">Underline</td>
                        <td style="padding: 12px 0; color: var(--color-text-secondary, #475569);">3px outline</td>
                        <td style="padding: 12px 0; color: var(--color-text-secondary, #475569);">Sage pastel + Ink-700</td>
                    </tr>
                    <tr style="border-bottom: 1px solid var(--color-border-default, #E2E8F0);">
                        <td style="padding: 12px 0; color: var(--color-text-primary, #1E293B); font-weight: 500;">Navigation</td>
                        <td style="padding: 12px 0; color: var(--color-text-secondary, #475569);">Underline (NO fill)</td>
                        <td style="padding: 12px 0; color: var(--color-text-secondary, #475569);">3px outline</td>
                        <td style="padding: 12px 0; color: var(--color-text-tertiary, #64748B);">—</td>
                    </tr>
                    <tr style="border-bottom: 1px solid var(--color-border-default, #E2E8F0);">
                        <td style="padding: 12px 0; color: var(--color-text-primary, #1E293B); font-weight: 500;">Accordions</td>
                        <td style="padding: 12px 0; color: var(--color-text-secondary, #475569);">Underline</td>
                        <td style="padding: 12px 0; color: var(--color-text-secondary, #475569);">3px outline</td>
                        <td style="padding: 12px 0; color: var(--color-text-secondary, #475569);">Chevron rotation</td>
                    </tr>
                    <tr style="border-bottom: 1px solid var(--color-border-default, #E2E8F0);">
                        <td style="padding: 12px 0; color: var(--color-text-primary, #1E293B); font-weight: 500;">Cards</td>
                        <td style="padding: 12px 0; color: var(--color-text-secondary, #475569);">Shadow lift + transform</td>
                        <td style="padding: 12px 0; color: var(--color-text-secondary, #475569);">Focus within elements</td>
                        <td style="padding: 12px 0; color: var(--color-text-tertiary, #64748B);">—</td>
                    </tr>
                    <tr>
                        <td style="padding: 12px 0; color: var(--color-text-primary, #1E293B); font-weight: 500;">Tables</td>
                        <td style="padding: 12px 0; color: var(--color-text-secondary, #475569);">Light background (#F8FAFC)</td>
                        <td style="padding: 12px 0; color: var(--color-text-secondary, #475569);">Row outline</td>
                        <td style="padding: 12px 0; color: var(--color-text-tertiary, #64748B);">—</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </section>
    
</div>

<script>
// Reduced Motion Toggle
(function() {
    const toggle = document.getElementById('reduced-motion-toggle');
    
    toggle.addEventListener('change', function() {
        if (this.checked) {
            document.body.classList.add('reduced-motion-override');
            console.log('Reduced motion enabled - all transitions/animations set to 0.01ms');
        } else {
            document.body.classList.remove('reduced-motion-override');
            console.log('Reduced motion disabled - normal transitions/animations restored');
        }
    });
    
    // Log initial state
    console.log('C1 Styleguide cargada. Usa el selector "Movimiento Reducido" para probar el comportamiento de animación.');
})();
</script>

<?php wp_footer(); ?>
</body>
</html>
