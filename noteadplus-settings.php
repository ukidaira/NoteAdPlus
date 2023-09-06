<?php
// プラグインファイルへの直接アクセスを防止
if (!defined('ABSPATH')) {
    exit; // 直接アクセスが試みられた場合は終了
}

// 設定ページのコンテンツを作成
function custom_ad_plugin_settings_page() {
    ?>
    <div class="wrap">
        <h2>カスタム広告表示設定</h2>
        <form method="post" action="options.php">
            <?php
            settings_fields('custom_ad_plugin_options'); // オプション名を指定して設定フィールドを出力
            do_settings_sections('custom_ad_display'); // セクション名を指定して設定セクションを出力
            submit_button(); // 保存ボタンを出力
            ?>
        </form>
    </div>
    <?php
}

// 広告テキスト設定のコールバック関数
function custom_ad_plugin_text_callback() {
    $options = get_option('custom_ad_plugin_options');
    $text = isset($options['ad_text']) ? $options['ad_text'] : '';
    echo '<textarea name="custom_ad_plugin_options[ad_text]" rows="5" cols="50">' . esc_textarea($text) . '</textarea>';
}

// 枠線スタイル設定のコールバック関数
function custom_ad_plugin_border_style_callback() {
    $options = get_option('custom_ad_plugin_options');
    $border_style = isset($options['border_style']) ? $options['border_style'] : 'solid';
    $styles = array('none', 'solid', 'dotted', 'dashed', 'double', 'groove', 'ridge', 'inset', 'outset');
    echo '<select name="custom_ad_plugin_options[border_style]">';
    foreach($styles as $style) {
        echo '<option value="' . $style . '" ' . selected($border_style, $style, false) . '>' . ucfirst($style) . '</option>';
    }
    echo '</select>';
}

// 表示トグル設定のコールバック関数
function custom_ad_plugin_display_toggle_callback() {
    $options = get_option('custom_ad_plugin_options');
    $display = isset($options['display']) ? $options['display'] : 'on';
    echo '<input type="radio" name="custom_ad_plugin_options[display]" value="on" ' . checked($display, 'on', false) . '> 表示';
    echo '<input type="radio" name="custom_ad_plugin_options[display]" value="off" ' . checked($display, 'off', false) . '> 非表示';
}

// 設定の登録と定義
function custom_ad_plugin_admin_init(){
    register_setting('custom_ad_plugin_options', 'custom_ad_plugin_options', 'custom_ad_plugin_sanitize_options'); // オプション名、オプションの検証用関数を指定して設定を登録

    add_settings_section('custom_ad_plugin_main', 'メイン設定', 'custom_ad_plugin_section_text', 'custom_ad_display'); // 設定セクションを追加
    add_settings_field('custom_ad_plugin_position', '表示位置', 'custom_ad_plugin_position_callback', 'custom_ad_display', 'custom_ad_plugin_main'); // 表示位置設定のフィールドを追加
    add_settings_field('custom_ad_plugin_text', '表示テキスト', 'custom_ad_plugin_text_callback', 'custom_ad_display', 'custom_ad_plugin_main'); // 表示テキスト設定のフィールドを追加
    add_settings_field('custom_ad_plugin_border_style', '枠線のスタイル', 'custom_ad_plugin_border_style_callback', 'custom_ad_display', 'custom_ad_plugin_main'); // 枠線スタイル設定のフィールドを追加
	// 他の設定項目のフィールドも追加可能
}

add_action('admin_init', 'custom_ad_plugin_admin_init'); // 管理画面の初期化時に関数を実行

// 記事の選択設定のコールバック関数
function custom_ad_plugin_post_selection_callback() {
    $options = get_option('custom_ad_plugin_options');
    $selected_posts = isset($options['selected_posts']) ? $options['selected_posts'] : array();

    // 最新の50件の投稿を取得
    $args = array(
        'numberposts' => 50,
        'post_type'   => 'post',
        'post_status' => 'publish',
    );
    $recent_posts = get_posts($args);

    foreach ($recent_posts as $post) {
        echo '<input type="checkbox" name="custom_ad_plugin_options[selected_posts][]" value="' . $post->ID . '" ' . (in_array($post->ID, $selected_posts) ? 'checked' : '') . '> ' . $post->post_title . '<br>';
    }
}

// 枠線の色設定のコールバック関数
function custom_ad_plugin_border_color_callback() {
    $options = get_option('custom_ad_plugin_options');
    $color = isset($options['border_color']) ? $options['border_color'] : '#000000';
    echo '<input type="text" class="color-picker" name="custom_ad_plugin_options[border_color]" value="' . esc_attr($color) . '">';
    echo '<button type="button" class="reset-color" data-default-color="#000000">デフォルトに戻す</button>';
}

// 背景色設定のコールバック関数
function custom_ad_plugin_bg_color_callback() {
    $options = get_option('custom_ad_plugin_options');
    $color = isset($options['bg_color']) ? $options['bg_color'] : '#FFFFFF';
    echo '<input type="text" class="color-picker" name="custom_ad_plugin_options[bg_color]" value="' . esc_attr($color) . '">';
    echo '<button type="button" class="reset-color" data-default-color="#FFFFFF">デフォルトに戻す</button>';
}

// 文字色設定のコールバック関数
function custom_ad_plugin_text_color_callback() {
    $options = get_option('custom_ad_plugin_options');
    $color = isset($options['text_color']) ? $options['text_color'] : '#000000';
    echo '<input type="text" class="color-picker" name="custom_ad_plugin_options[text_color]" value="' . esc_attr($color) . '">';
    echo '<button type="button" class="reset-color" data-default-color="#000000">デフォルトに戻す</button>';
}

// 幅設定のコールバック関数
function custom_ad_plugin_width_callback() {
    $options = get_option('custom_ad_plugin_options');
    $width = isset($options['width']) ? $options['width'] : '100'; // デフォルトの幅は100pxに設定
    echo '<input type="text" name="custom_ad_plugin_options[width]" value="' . esc_attr($width) . '"> px';
}

// フォントサイズ設定のコールバック関数
function custom_ad_plugin_font_size_callback() {
    $options = get_option('custom_ad_plugin_options');
    $fontSize = isset($options['font_size']) ? $options['font_size'] : '16';
    echo '<input type="text" name="custom_ad_plugin_options[font_size]" value="' . esc_attr($fontSize) . '"> px';
}

// 枠の角丸設定のコールバック関数
function custom_ad_plugin_border_radius_callback() {
    $options = get_option('custom_ad_plugin_options');
    $borderRadius = isset($options['border_radius']) ? $options['border_radius'] : '0';
    echo '<input type="text" name="custom_ad_plugin_options[border_radius]" value="' . esc_attr($borderRadius) . '"> px';
}

// 枠の太さ設定のコールバック関数
function custom_ad_plugin_border_width_callback() {
    $options = get_option('custom_ad_plugin_options');
    $borderWidth = isset($options['border_width']) ? $options['border_width'] : '1';
    echo '<input type="text" name="custom_ad_plugin_options[border_width]" value="' . esc_attr($borderWidth) . '"> px';
}

// マージン設定のコールバック関数
function custom_ad_plugin_margin_callback() {
    $options = get_option('custom_ad_plugin_options');
    $margin = isset($options['margin']) ? $options['margin'] : '10';
    echo '<input type="text" name="custom_ad_plugin_options[margin]" value="' . esc_attr($margin) . '"> px';
}

// パディング設定のコールバック関数
function custom_ad_plugin_padding_callback() {
    $options = get_option('custom_ad_plugin_options');
    $padding = isset($options['padding']) ? $options['padding'] : '10';
    echo '<input type="text" name="custom_ad_plugin_options[padding]" value="' . esc_attr($padding) . '"> px';
}

// ボックスの位置設定のコールバック関数
function custom_ad_plugin_box_align_callback() {
    $options = get_option('custom_ad_plugin_options');
    $boxAlign = isset($options['box_align']) ? $options['box_align'] : 'center';
    $alignments = array('left', 'center', 'right');
    echo '<select name="custom_ad_plugin_options[box_align]">';
    foreach ($alignments as $alignment) {
        echo '<option value="' . $alignment . '"' . selected($boxAlign, $alignment, false) . '>' . ucfirst($alignment) . '</option>';
    }
    echo '</select>';
}

// 入力を正規化して検証
function custom_ad_plugin_sanitize_options($input) {
    $output = array();
    foreach ($input as $key => $value) {
        if ($key == 'selected_posts' && is_array($value)) {
            $output[$key] = array_map('absint', $value);  // すべての値が正の整数であることを確認
        } elseif ($key == 'font_size' || $key == 'border_radius' || $key == 'border_width' || $key == 'margin' || $key == 'padding' || $key == 'width') {
            $output[$key] = intval($input[$key]);
        } else if (isset($input[$key])) {
            $output[$key] = strip_tags(stripslashes($input[$key]));
        }
    }
    return $output;
}

// メイン設定セクションの表示テキスト
function custom_ad_plugin_section_text() {
    echo '<p>カスタム広告が表示される場所と方法を選択してください。</p>';
}

// 広告表示位置設定のコールバック関数
function custom_ad_plugin_position_callback() {
    $options = get_option('custom_ad_plugin_options');
    $position = isset($options['position']) ? $options['position'] : 'above_title';
    echo '<select name="custom_ad_plugin_options[position]">';
    echo '<option value="above_title" ' . selected($position, 'above_title', false) . '>記事の上</option>';
    echo '<option value="below_title" ' . selected($position, 'below_title', false) . '>記事の下</option>';
    // ... 追加の表示位置をここに追加可能
    echo '</select>';
}

// 設定に基づいてコンテンツにカスタム広告を追加するフィルター
function custom_ad_plugin_display_ad($content) {
    $options = get_option('custom_ad_plugin_options');
    $ad_text = isset($options['ad_text']) ? $options['ad_text'] : '広告';

    $border_style = isset($options['border_style']) ? $options['border_style'] : 'solid';
    $border_color = isset($options['border_color']) ? $options['border_color'] : '#000000';
    $bg_color = isset($options['bg_color']) ? $options['bg_color'] : '#FFFFFF';
    $text_color = isset($options['text_color']) ? $options['text_color'] : '#000000';
    $fontSize = isset($options['font_size']) ? $options['font_size'] : '16';
    $borderRadius = isset($options['border_radius']) ? $options['border_radius'] : '0';
    $borderWidth = isset($options['border_width']) ? $options['border_width'] : '1';
    $margin = isset($options['margin']) ? $options['margin'] : '10';
    $padding = isset($options['padding']) ? $options['padding'] : '10';
    $textAlign = isset($options['text_align']) ? $options['text_align'] : 'center';
    $width = isset($options['width']) ? $options['width'] : '100';

    $ad_style = "width: {$width}px; border: {$borderWidth}px {$border_style} {$border_color}; background-color: {$bg_color}; color: {$text_color}; font-size: {$fontSize}px; border-radius: {$borderRadius}px; margin: {$margin}px; padding: {$padding}px; text-align: {$textAlign};";

    $container_style = "";
    $boxAlign = isset($options['box_align']) ? $options['box_align'] : 'center';

    if ($boxAlign === 'left') {
        $container_style = "text-align: left;";
    } elseif ($boxAlign === 'right') {
        $container_style = "text-align: right;";
    } else {
        $container_style = "text-align: center;";
    }

    $ad_html = '<div class="ad-container ' . $boxAlign . '">
                <div class="custom-content-label" style="' . $ad_style . '">' . $ad_text . '</div>
            </div>';

    if (isset($options['position']) && $options['position'] == 'above_title') {
        return $ad_html . $content;
    } else {
        return $content . $ad_html;
    }
}

add_filter('the_content', 'custom_ad_plugin_display_ad'); // コンテンツにカスタム広告を表示するフィルターを追加
