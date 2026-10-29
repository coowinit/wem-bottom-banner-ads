<?php
/**
 * Plugin Name: WEM Bottom Banner Ads
 * Description: 轻量级底部响应式横幅广告，支持首页显示、PC/移动端图片、后台开关及关闭状态记忆。
 * Version: 1.0.3
 * Requires at least: 6.0
 * Requires PHP: 7.4
 * Author: WEM
 * Text Domain: wem-bottom-banner-ads
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

define( 'WEM_BBA_VERSION', '1.0.3' );
define( 'WEM_BBA_PATH', plugin_dir_path( __FILE__ ) );
define( 'WEM_BBA_URL', plugin_dir_url( __FILE__ ) );

function wem_bba_defaults() {
    return array(
        'enabled'      => 0,
        'scope'        => 'home',
        'desktop_id'   => 0,
        'mobile_id'    => 0,
        'link'         => '',
        'new_tab'      => 1,
        'alt'          => 'Promotional banner',
        'revision'     => 1,
    );
}

function wem_bba_settings() {
    $saved = get_option( 'wem_bba_settings', array() );
    return wp_parse_args( is_array( $saved ) ? $saved : array(), wem_bba_defaults() );
}

register_activation_hook( __FILE__, function () {
    if ( false === get_option( 'wem_bba_settings', false ) ) {
        add_option( 'wem_bba_settings', wem_bba_defaults() );
    }
} );

// 在「插件 → 已安装插件」列表中提供快捷设置入口。
add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), 'wem_bba_action_links' );
function wem_bba_action_links( $links ) {
    if ( current_user_can( 'manage_options' ) ) {
        $settings_url = admin_url( 'options-general.php?page=wem-bottom-banner-ads' );
        array_unshift( $links, '<a href="' . esc_url( $settings_url ) . '">设置</a>' );
    }
    return $links;
}

require_once WEM_BBA_PATH . 'includes/admin-settings.php';
require_once WEM_BBA_PATH . 'includes/frontend-banner.php';
