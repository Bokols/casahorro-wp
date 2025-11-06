<?php
/**
 * Theme Customizer: Ribbon image, tagline and mobile density.
 *
 * - Image control: 'cas_ribbon_image' (Media image URL)
 * - Text control: 'cas_ribbon_tagline' (sanitize_text_field)
 * - Select control: 'cas_mobile_density' (comfortable|cozy|compact)
 *
 * Also adds a body_class reflecting the chosen density:
 *  - is-density-comfortable
 *  - is-density-cozy
 *  - is-density-compact
 *
 * PHP version 8.2
 *
 * @package Hello_Elementor_Child
 */

declare( strict_types=1 );

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Direct access protection.
}

/**
 * Register Customizer settings and controls.
 *
 * @param WP_Customize_Manager $wp_customize Customizer manager instance.
 * @return void
 */
function hello_elementor_child_customizer_register( WP_Customize_Manager $wp_customize ): void {
    // Add a custom panel (optional) or you can group under 'title_tagline' etc.
    $panel_id = 'hello_elementor_child_panel';
    if ( ! $wp_customize->get_panel( $panel_id ) ) {
        $wp_customize->add_panel(
            $panel_id,
            array(
                'title'       => __( 'Apariencia del tema (casAhorro)', 'hello-elementor-child' ),
                'description' => __( 'Ajustes rápidos: cinta, etiqueta y densidad móvil.', 'hello-elementor-child' ),
                'priority'    => 160,
            )
        );
    }

    //
    // Section: Ribbon (Cinta)
    //
    $section_ribbon = 'hello_elementor_child_section_ribbon';
    if ( ! $wp_customize->get_section( $section_ribbon ) ) {
        $wp_customize->add_section(
            $section_ribbon,
            array(
                'title'    => __( 'Cinta (Ribbon)', 'hello-elementor-child' ),
                'panel'    => $panel_id,
                'priority' => 10,
            )
        );
    }

    // Setting: Ribbon image (stores a URL). Default: empty (none).
    $wp_customize->add_setting(
        'cas_ribbon_image',
        array(
            'default'           => '',
            'sanitize_callback' => 'esc_url_raw',
            'transport'         => 'refresh',
            // theme_mod is default; explicit for clarity
            'type'              => 'theme_mod',
        )
    );

    // Control: Image picker
    $wp_customize->add_control(
        new WP_Customize_Image_Control(
            $wp_customize,
            'cas_ribbon_image_ctrl',
            array(
                'label'       => __( 'Imagen de la cinta', 'hello-elementor-child' ),
                'section'     => $section_ribbon,
                'settings'    => 'cas_ribbon_image',
                'description' => __( 'Selecciona una imagen desde la Biblioteca de Medios para mostrar en la cinta (opcional).', 'hello-elementor-child' ),
            )
        )
    );

    // Setting: Ribbon tagline (text)
    $wp_customize->add_setting(
        'cas_ribbon_tagline',
        array(
            'default'           => 'Cuidamos tu casa y tu presupuesto',
            'sanitize_callback' => 'sanitize_text_field',
            'transport'         => 'refresh',
            'type'              => 'theme_mod',
        )
    );

    // Control: Text input for tagline
    $wp_customize->add_control(
        'cas_ribbon_tagline_ctrl',
        array(
            'label'    => __( 'Etiqueta de la cinta', 'hello-elementor-child' ),
            'section'  => $section_ribbon,
            'settings' => 'cas_ribbon_tagline',
            'type'     => 'text',
        )
    );

    //
    // Section: Mobile density
    //
    $section_density = 'hello_elementor_child_section_density';
    if ( ! $wp_customize->get_section( $section_density ) ) {
        $wp_customize->add_section(
            $section_density,
            array(
                'title'    => __( 'Densidad (móvil)', 'hello-elementor-child' ),
                'panel'    => $panel_id,
                'priority' => 20,
            )
        );
    }

    // Allowed choices and default
    $density_choices = array(
        'comfortable' => __( 'Cómodo (48px targets)', 'hello-elementor-child' ),
        'cozy'        => __( 'Intermedio (44px targets)', 'hello-elementor-child' ),
        'compact'     => __( 'Compacto (40–44px targets)', 'hello-elementor-child' ),
    );
    $density_default = 'cozy';

    // Setting: mobile density
    $wp_customize->add_setting(
        'cas_mobile_density',
        array(
            'default'           => $density_default,
            'sanitize_callback' => 'hello_elementor_child_sanitize_density',
            'transport'         => 'refresh',
            'type'              => 'theme_mod',
        )
    );

    // Control: select for density
    $wp_customize->add_control(
        'cas_mobile_density_ctrl',
        array(
            'label'    => __( 'Densidad en móvil', 'hello-elementor-child' ),
            'section'  => $section_density,
            'settings' => 'cas_mobile_density',
            'type'     => 'select',
            'choices'  => $density_choices,
        )
    );
}
add_action( 'customize_register', 'hello_elementor_child_customizer_register', 20 );

/**
 * Sanitization callback for cas_mobile_density.
 *
 * Ensures only expected values are returned; falls back to the default.
 *
 * @param string|null $value Raw value from Customizer.
 * @return string Sanitized density slug.
 */
function hello_elementor_child_sanitize_density( $value ): string {
    $allowed = array( 'comfortable', 'cozy', 'compact' );
    if ( ! is_string( $value ) ) {
        return 'cozy';
    }
    $value = trim( $value );
    if ( in_array( $value, $allowed, true ) ) {
        return $value;
    }
    return 'cozy';
}

/**
 * Append density class to body_class array based on customizer setting.
 *
 * Adds one of:
 *  - is-density-comfortable
 *  - is-density-cozy
 *  - is-density-compact
 *
 * @param array $classes Existing body classes.
 * @return array Modified body classes.
 */
function hello_elementor_child_body_density_class( array $classes ): array {
    $density = get_theme_mod( 'cas_mobile_density', 'cozy' );
    $density = hello_elementor_child_sanitize_density( $density );

    $map = array(
        'comfortable' => 'is-density-comfortable',
        'cozy'        => 'is-density-cozy',
        'compact'     => 'is-density-compact',
    );

    if ( isset( $map[ $density ] ) ) {
        $classes[] = $map[ $density ];
    }

    return $classes;
}
add_filter( 'body_class', 'hello_elementor_child_body_density_class', 10, 1 );

/**
 * Helpers to fetch ribbon values safely.
 *
 * These small helpers are optional but provide a consistent API.
 */

/**
 * Get ribbon image URL (escaped).
 *
 * @return string Empty or safe URL.
 */
function hello_elementor_child_get_ribbon_image(): string {
    $url = get_theme_mod( 'cas_ribbon_image', '' );
    if ( ! is_string( $url ) || '' === $url ) {
        return '';
    }
    return esc_url( $url );
}

/**
 * Get ribbon tagline (escaped).
 *
 * @return string Safe tagline string.
 */
function hello_elementor_child_get_ribbon_tagline(): string {
    $text = get_theme_mod( 'cas_ribbon_tagline', 'Cuidamos tu casa y tu presupuesto' );
    if ( ! is_string( $text ) ) {
        $text = 'Cuidamos tu casa y tu presupuesto';
    }
    return esc_html( $text );
}