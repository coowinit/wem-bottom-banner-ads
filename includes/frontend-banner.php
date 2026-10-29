<?php
if ( ! defined( 'ABSPATH' ) ) { exit; }

function wem_bba_should_render() {
    if ( is_admin() || is_feed() || is_robots() || is_embed() ) { return false; }
    $settings = wem_bba_settings();
    if ( empty( $settings['enabled'] ) || empty( $settings['desktop_id'] ) ) { return false; }
    if ( 'all' !== $settings['scope'] && ! is_front_page() ) { return false; }
    return (bool) wp_get_attachment_image_url( $settings['desktop_id'], 'full' );
}

add_action( 'wp_enqueue_scripts', function () {
    if ( ! wem_bba_should_render() ) { return; }
    wp_enqueue_style( 'wem-bba-banner', WEM_BBA_URL . 'assets/css/banner.css', array(), WEM_BBA_VERSION );
    wp_enqueue_script( 'wem-bba-banner', WEM_BBA_URL . 'assets/js/banner.js', array(), WEM_BBA_VERSION, true );
} );

add_action( 'wp_footer', function () {
    if ( ! wem_bba_should_render() ) { return; }
    $s = wem_bba_settings();
    $desktop = wp_get_attachment_image_url( $s['desktop_id'], 'full' );
    $mobile = $s['mobile_id'] ? wp_get_attachment_image_url( $s['mobile_id'], 'full' ) : '';
    $desktop_meta = wp_get_attachment_metadata( $s['desktop_id'] );
    $width = ! empty( $desktop_meta['width'] ) ? absint( $desktop_meta['width'] ) : 1200;
    $height = ! empty( $desktop_meta['height'] ) ? absint( $desktop_meta['height'] ) : 200;
    $url = $s['link'];
    $token = substr( hash( 'sha256', $s['revision'] . '|' . $s['desktop_id'] . '|' . $s['mobile_id'] . '|' . $url ), 0, 16 );
    ?>
    <aside class="wem-bba-banner" data-banner-version="<?php echo esc_attr( $token ); ?>" aria-label="Advertisement">
        <button type="button" class="wem-bba-banner__close" aria-label="Close advertisement">
            <img src="<?php echo esc_url( WEM_BBA_URL . 'assets/images/close-button.svg' ); ?>" alt="" width="30" height="30">
        </button>
        <?php if ( $url ) : ?>
            <a class="wem-bba-banner__link" href="<?php echo esc_url( $url ); ?>" <?php if ( $s['new_tab'] ) : ?>target="_blank" rel="noopener noreferrer"<?php endif; ?>>
        <?php else : ?>
            <div class="wem-bba-banner__link">
        <?php endif; ?>
            <picture>
                <?php if ( $mobile ) : ?><source media="(max-width: 640px)" srcset="<?php echo esc_url( $mobile ); ?>"><?php endif; ?>
                <img src="<?php echo esc_url( $desktop ); ?>" alt="<?php echo esc_attr( $s['alt'] ); ?>" width="<?php echo esc_attr( $width ); ?>" height="<?php echo esc_attr( $height ); ?>" decoding="async">
            </picture>
        <?php if ( $url ) : ?></a><?php else : ?></div><?php endif; ?>
    </aside>
    <?php
}, 5 );
