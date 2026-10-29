<?php
if ( ! defined( 'ABSPATH' ) ) { exit; }

add_action( 'admin_menu', function () {
    add_options_page( 'WEM Bottom Banner Ads', 'WEM Banner Ads', 'manage_options', 'wem-bottom-banner-ads', 'wem_bba_render_settings' );
} );

add_action( 'admin_init', function () {
    register_setting( 'wem_bba_group', 'wem_bba_settings', array(
        'type' => 'array',
        'sanitize_callback' => 'wem_bba_sanitize_settings',
        'default' => wem_bba_defaults(),
    ) );
} );

function wem_bba_sanitize_settings( $input ) {
    $old = wem_bba_settings();
    $input = is_array( $input ) ? $input : array();
    $new = array(
        'enabled' => empty( $input['enabled'] ) ? 0 : 1,
        'scope' => isset( $input['scope'] ) && 'all' === $input['scope'] ? 'all' : 'home',
        'desktop_id' => isset( $input['desktop_id'] ) ? absint( $input['desktop_id'] ) : 0,
        'mobile_id' => isset( $input['mobile_id'] ) ? absint( $input['mobile_id'] ) : 0,
        'link' => isset( $input['link'] ) ? esc_url_raw( trim( (string) $input['link'] ) ) : '',
        'new_tab' => empty( $input['new_tab'] ) ? 0 : 1,
        'alt' => isset( $input['alt'] ) ? sanitize_text_field( $input['alt'] ) : '',
        'revision' => (int) $old['revision'],
    );
    foreach ( array( 'desktop_id', 'mobile_id' ) as $key ) {
        if ( $new[ $key ] && ! wp_attachment_is_image( $new[ $key ] ) ) {
            $new[ $key ] = 0;
        }
    }
    // 启用状态、展示范围、图片或链接变化时，刷新关闭记忆的版本。
    foreach ( array( 'enabled', 'scope', 'desktop_id', 'mobile_id', 'link' ) as $key ) {
        if ( (string) $new[ $key ] !== (string) $old[ $key ] ) {
            $new['revision']++;
            break;
        }
    }
    return $new;
}

add_action( 'admin_enqueue_scripts', function ( $hook ) {
    if ( 'settings_page_wem-bottom-banner-ads' !== $hook ) { return; }
    wp_enqueue_media();
    wp_enqueue_script( 'wem-bba-admin', WEM_BBA_URL . 'assets/js/admin.js', array(), WEM_BBA_VERSION, true );
} );

function wem_bba_media_field( $key, $id, $label, $recommended ) {
    $url = $id ? wp_get_attachment_image_url( $id, 'medium' ) : '';
    ?>
    <tr>
        <th scope="row"><label for="wem-bba-<?php echo esc_attr( $key ); ?>"><?php echo esc_html( $label ); ?></label></th>
        <td>
            <div class="wem-bba-media" data-field="<?php echo esc_attr( $key ); ?>">
                <input id="wem-bba-<?php echo esc_attr( $key ); ?>" type="hidden" name="wem_bba_settings[<?php echo esc_attr( $key ); ?>]" value="<?php echo esc_attr( $id ); ?>">
                <img class="wem-bba-preview" src="<?php echo esc_url( $url ); ?>" alt="" style="max-width:360px;max-height:150px;display:<?php echo $url ? 'block' : 'none'; ?>;margin-bottom:10px;object-fit:contain">
                <button type="button" class="button wem-bba-select">选择图片</button>
                <button type="button" class="button wem-bba-remove">清除图片</button>
            </div>
            <p class="description">推荐尺寸：<?php echo esc_html( $recommended ); ?>。不会强制裁切，前端等比例缩放。</p>
        </td>
    </tr>
    <?php
}

function wem_bba_render_settings() {
    if ( ! current_user_can( 'manage_options' ) ) { return; }
    $s = wem_bba_settings();
    ?>
    <div class="wrap">
        <h1>WEM Bottom Banner Ads</h1>
        <p>响应式底部横幅广告。首次安装默认关闭，仅首页展示；图片由 WordPress 媒体库管理。</p>
        <form method="post" action="options.php">
            <?php settings_fields( 'wem_bba_group' ); ?>
            <table class="form-table" role="presentation">
                <tr><th scope="row">启用广告</th><td>
                    <label><input type="checkbox" name="wem_bba_settings[enabled]" value="1" <?php checked( $s['enabled'], 1 ); ?>> 启用底部横幅广告</label>
                </td></tr>
                <tr><th scope="row"><label for="wem-bba-scope">显示范围</label></th><td>
                    <select id="wem-bba-scope" name="wem_bba_settings[scope]">
                        <option value="home" <?php selected( $s['scope'], 'home' ); ?>>仅首页（默认）</option>
                        <option value="all" <?php selected( $s['scope'], 'all' ); ?>>全站</option>
                    </select>
                </td></tr>
                <?php wem_bba_media_field( 'desktop_id', $s['desktop_id'], 'PC 广告图片', '1200 × 200 px' ); ?>
                <?php wem_bba_media_field( 'mobile_id', $s['mobile_id'], '手机广告图片', '750 × 220 px' ); ?>
                <tr><th scope="row"><label for="wem-bba-link">广告点击链接</label></th><td>
                    <input id="wem-bba-link" type="url" class="regular-text" name="wem_bba_settings[link]" value="<?php echo esc_attr( $s['link'] ); ?>" placeholder="https://example.com/landing-page/">
                    <p class="description">填写完整 URL。未设置链接时仅展示图片，不允许点击跳转。</p>
                </td></tr>
                <tr><th scope="row">打开方式</th><td>
                    <label><input type="checkbox" name="wem_bba_settings[new_tab]" value="1" <?php checked( $s['new_tab'], 1 ); ?>> 在新标签页打开广告链接</label>
                </td></tr>
                <tr><th scope="row"><label for="wem-bba-alt">图片替代文字（ALT）</label></th><td>
                    <input id="wem-bba-alt" type="text" class="regular-text" name="wem_bba_settings[alt]" value="<?php echo esc_attr( $s['alt'] ); ?>" placeholder="Promotional banner">
                </td></tr>
            </table>
            <?php submit_button( '保存设置' ); ?>
        </form>
        <p><strong>说明：</strong>PC 最大宽度为 1200px，手机切换断点为 640px，距离底部 20px。访客关闭广告后，在当前标签页会话中不再显示；更换图片或链接后重新显示。前台页面缓存可能需要清理。</p>
    </div>
    <?php
}
