<?php
/**
 * Template Part: Post/Page Card (Search Results Fallback)
 *
 * Simple card for non-product search results (posts, pages).
 *
 * PHP version 8.2
 *
 * @package Hello_Elementor_Child
 */

declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

?>

<article <?php post_class( 'post-card search-result-post' ); ?>>
    <div class="post-card-inner">
        
        <?php if ( has_post_thumbnail() ) : ?>
            <div class="post-card-image">
                <a href="<?php the_permalink(); ?>">
                    <?php the_post_thumbnail( 'medium' ); ?>
                </a>
            </div>
        <?php endif; ?>

        <div class="post-card-content">
            
            <!-- Post Type Label -->
            <span class="post-type-badge">
                <?php
                $post_type_obj = get_post_type_object( get_post_type() );
                echo esc_html( $post_type_obj->labels->singular_name ?? get_post_type() );
                ?>
            </span>

            <!-- Title -->
            <h3 class="post-card-title">
                <a href="<?php the_permalink(); ?>">
                    <?php the_title(); ?>
                </a>
            </h3>

            <!-- Excerpt -->
            <?php if ( has_excerpt() || get_the_content() ) : ?>
                <div class="post-card-excerpt">
                    <?php echo wp_kses_post( wp_trim_words( get_the_excerpt(), 20 ) ); ?>
                </div>
            <?php endif; ?>

            <!-- Read More -->
            <div class="post-card-actions">
                <a href="<?php the_permalink(); ?>" class="button button-secondary">
                    <?php esc_html_e( 'Leer más', 'hello-elementor-child' ); ?>
                </a>
            </div>
        </div>
    </div>
</article>
