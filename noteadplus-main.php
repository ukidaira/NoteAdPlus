<?php
// プラグインファイルへの直接アクセスを防止
if (!defined('ABSPATH')) {
    exit; // 直接アクセスが試みられた場合は終了
}

/*
Plugin Name: NoteAdPlus
Description: 景品表示法に対応するためのテキスト表示を管理するプラグイン
Version: 1.0.6
Author: ukidaira
*/

require 'updater/plugin-update-checker.php';
use YahnisElsts\PluginUpdateChecker\v5\PucFactory;

$myUpdateChecker = PucFactory::buildUpdateChecker(
	'https://github.com/ukidaira/NoteAdPlus/',
	__FILE__,
	'NoteAdPlus'
);

$myUpdateChecker->setBranch('main');


// 管理画面用のスタイルとスクリプト
function custom_ad_plugin_admin_scripts() {
    wp_enqueue_style('custom-content-label-plugin-admin', plugins_url('frontend-style.css', __FILE__));
    wp_enqueue_script('custom-content-label-plugin-admin', plugins_url('noteadplus-scripts.js', __FILE__), array('jquery'));
    wp_enqueue_style('custom-ad-plugin-customizer-style', plugins_url('customizer-style.css', __FILE__));
    wp_enqueue_style('wp-color-picker');
    wp_enqueue_script('wp-color-picker');
}
add_action('admin_enqueue_scripts', 'custom_ad_plugin_admin_scripts');

// フロントエンド用のスタイルをエンキュー
function custom_ad_plugin_frontend_scripts() {
    wp_enqueue_style('custom-content-label-plugin-frontend', plugins_url('frontend-style.css', __FILE__));
}
add_action('wp_enqueue_scripts', 'custom_ad_plugin_frontend_scripts', 100);

/// カスタム広告を表示するショートコード
function custom_ad_shortcode_function() {
    $options = get_option('custom_ad_plugin_options');
    // テキストの表示設定を確認
    if (isset($options['display']) && $options['display'] == 'off') {
        return ''; // テキストを表示しない場合、何も返さない
    }
    
    $ad_text = isset($options['ad_text']) ? $options['ad_text'] : '広告'; 
    $border_style = isset($options['border_style']) ? $options['border_style'] : 'solid';
    $border_color = isset($options['border_color']) ? $options['border_color'] : '#000000';
    $bg_color = isset($options['bg_color']) ? $options['bg_color'] : '#FFFFFF';
    $text_color = isset($options['text_color']) ? $options['text_color'] : '#000000';
    $fontSize = isset($options['font_size']) ? $options['font_size'] : '14';
    $borderRadius = isset($options['border_radius']) ? $options['border_radius'] : '0';
    $borderWidth = isset($options['border_width']) ? $options['border_width'] : '1';
    $margin = isset($options['margin']) ? $options['margin'] : '0';
    $padding = isset($options['padding']) ? $options['padding'] : '0';
    $textAlign = isset($options['text_align']) ? $options['text_align'] : 'center';
    $width = isset($options['width']) ? $options['width'] : '6'; 
    $boxAlign = isset($options['box_align']) ? $options['box_align'] : 'left';
    $margin_values = explode(',', $options['margin']);
    $margin_style = implode('px ', $margin_values) . 'px';
    $ad_style = "width: {$width}%; border: {$borderWidth}px {$border_style} {$border_color}; background-color: {$bg_color}; color: {$text_color}; font-size: {$fontSize}px; border-radius: {$borderRadius}px; margin: {$margin_style} !important; padding: {$padding}px; text-align: {$textAlign};";
    $ad_html = '<div class="ad-container ' . $boxAlign . '">
                    <div class="custom-content-label" style="' . $ad_style . '">' . $ad_text . '</div>
                </div>';
    
    return $ad_html;
}
add_shortcode('custom_ad', 'custom_ad_shortcode_function');

function custom_ad_add_to_content( $content ) {
    $options = get_option('custom_ad_plugin_options');
    
    // 選択された投稿のIDを取得
    $selected_post_ids = isset($options['displayed_post_checkbox']) ? explode(',', $options['displayed_post_checkbox']) : [];

    // 「全ての記事」が選択されている場合、全ての記事に表示
    if (in_array('all', $selected_post_ids)) {
    } else {
        // 現在の投稿IDが選択された投稿の中に含まれていない場合、広告を表示しない
        if (!empty($selected_post_ids) && !in_array(get_the_ID(), $selected_post_ids)) {
            return $content;
        }
    }
    
    // 広告の表示設定を確認
    if (isset($options['display']) && $options['display'] == 'off') {
        return $content; // 広告を表示しない設定の場合、コンテンツをそのまま返す
    }

    // 広告コードを生成
    $ad_content = do_shortcode('[custom_ad]');

    // 広告の表示位置に応じて、広告を追加
    if (isset($options['position'])) {
        switch ($options['position']) {
            case 'above_title':
                return $ad_content . $content;
            case 'below_title':
                // 実際のタイトルの直後に広告を挿入する方法はテーマによって異なるため、
                // ここでは単純にコンテンツの前に追加。
                return $content . $ad_content;
        }
    }

    return $content;
}
add_filter( 'the_content', 'custom_ad_add_to_content' );

function custom_ad_plugin_customize_register( $wp_customize ) {

class Posts_Checkbox_Custom_Control extends WP_Customize_Control {
    public $type = 'checkbox-posts';

    public function render_content() {
        $posts = get_posts(array('posts_per_page' => -1));

        echo '<span class="customize-control-title">' . esc_html($this->label) . '</span>';
        echo '<div style="height: 300px; overflow-y: scroll;">';

        // 「全ての記事」のチェックボックスを追加
        echo '<label>';
        echo '<input type="checkbox" class="post-checkbox all-posts-checkbox" value="all" ' . checked(in_array('all', explode(',', $this->value())), true, false) . ' /> ';
        echo '全ての記事';
        echo '</label>';
        echo '<br/>';

        foreach ($posts as $post) {
            echo '<label>';
            echo '<input type="checkbox" class="post-checkbox" value="' . esc_attr($post->ID) . '" ' . checked(in_array($post->ID, explode(',', $this->value())), true, false) . ' /> ';
            echo esc_html($post->post_title);
            echo '</label>';
            echo '<br/>';
        }

        echo '</div>';
        echo '<input type="hidden" value="' . esc_attr($this->value()) . '" ' . $this->get_link() . '>';
    }

    public function input_attrs() {
        return array('data-type' => 'checkbox-posts');
    }
}

    // セクションを追加
    $wp_customize->add_section('custom_ad_plugin_section', array(
        'title' => '広告注記設定',
        'priority' => 30,
    ));

    // 表示位置の設定とコントロールを追加
$wp_customize->add_setting('custom_ad_plugin_options[position]', array(
    'default' => 'above_title',
    'type' => 'option',
));
$wp_customize->add_control('custom_ad_plugin_position_control', array(
    'label' => '表示位置',
    'section' => 'custom_ad_plugin_section',
    'settings' => 'custom_ad_plugin_options[position]',
    'type' => 'select',
    'choices' => array(
        'above_title' => '記事の上',
        'below_title' => '記事の下',
    ),
));

// 広告テキストの設定とコントロールを追加
$wp_customize->add_setting('custom_ad_plugin_options[ad_text]', array(
    'default' => '広告',
    'type' => 'option',
));
$wp_customize->add_control('custom_ad_plugin_ad_text_control', array(
    'label' => '表示テキスト',
    'section' => 'custom_ad_plugin_section',
    'settings' => 'custom_ad_plugin_options[ad_text]',
    'type' => 'textarea',
));

// 幅の設定
$wp_customize->add_setting('custom_ad_plugin_options[width]', array(
    'default' => '6',  // ％をデフォルトに設定
    'type' => 'option',
));
$wp_customize->add_control('custom_ad_plugin_width_control', array(
    'label' => '幅（%）',
    'section' => 'custom_ad_plugin_section',
    'settings' => 'custom_ad_plugin_options[width]',
    'type' => 'number',
    'input_attrs' => array(
        'min' => 0,        // 0から100までの％値を許可
        'max' => 100,
        'step' => 1,
    ),
));

// 背景色の設定とコントロールを追加
$wp_customize->add_setting('custom_ad_plugin_options[bg_color]', array(
    'default' => '#FFFFFF',
    'type' => 'option',
));
$wp_customize->add_control(new WP_Customize_Color_Control($wp_customize, 'custom_ad_plugin_bg_color_control', array(
    'label' => '背景色',
    'section' => 'custom_ad_plugin_section',
    'settings' => 'custom_ad_plugin_options[bg_color]',
)));

// 文字色の設定とコントロールを追加
$wp_customize->add_setting('custom_ad_plugin_options[text_color]', array(
    'default' => '#333333',
    'type' => 'option',
));
$wp_customize->add_control(new WP_Customize_Color_Control($wp_customize, 'custom_ad_plugin_text_color_control', array(
    'label' => '文字色',
    'section' => 'custom_ad_plugin_section',
    'settings' => 'custom_ad_plugin_options[text_color]',
)));

// 枠線の色の設定とコントロールを追加
$wp_customize->add_setting('custom_ad_plugin_options[border_color]', array(
    'default' => '#333333',
    'type' => 'option',
));
$wp_customize->add_control(new WP_Customize_Color_Control($wp_customize, 'custom_ad_plugin_border_color_control', array(
    'label' => '枠線の色',
    'section' => 'custom_ad_plugin_section',
    'settings' => 'custom_ad_plugin_options[border_color]',
)));

// 枠線のスタイルの設定とコントロールを追加
$wp_customize->add_setting('custom_ad_plugin_options[border_style]', array(
    'default' => 'solid',
    'type' => 'option',
));
$wp_customize->add_control('custom_ad_plugin_border_style_control', array(
    'label' => '枠線のスタイル',
    'section' => 'custom_ad_plugin_section',
    'settings' => 'custom_ad_plugin_options[border_style]',
    'type' => 'select',
    'choices' => array(
        'none' => 'なし',
        'solid' => '実線',
        'dotted' => '点線',
        'dashed' => '破線',
    ),
));

// フォントサイズの設定
$wp_customize->add_setting('custom_ad_plugin_options[font_size]', array(
    'default' => '14',
    'type' => 'option',
));
$wp_customize->add_control('custom_ad_plugin_font_size_control', array(
    'label' => '表示テキストのフォントサイズ（px）',
    'section' => 'custom_ad_plugin_section',
    'settings' => 'custom_ad_plugin_options[font_size]',
    'type' => 'number',
    'input_attrs' => array(
        'min' => 8,
        'max' => 50,
        'step' => 1,
    ),
));

// 線の太さの設定
$wp_customize->add_setting('custom_ad_plugin_options[border_width]', array(
    'default' => '1',
    'type' => 'option',
));
$wp_customize->add_control('custom_ad_plugin_border_width_control', array(
    'label' => '線の太さ（px）',
    'section' => 'custom_ad_plugin_section',
    'settings' => 'custom_ad_plugin_options[border_width]',
    'type' => 'number',
    'input_attrs' => array(
        'min' => 1,
        'max' => 10,
        'step' => 1,
    ),
));

// 枠の角丸の設定
$wp_customize->add_setting('custom_ad_plugin_options[border_radius]', array(
    'default' => '0',
    'type' => 'option',
));
$wp_customize->add_control('custom_ad_plugin_border_radius_control', array(
    'label' => '枠の角丸（px）',
    'section' => 'custom_ad_plugin_section',
    'settings' => 'custom_ad_plugin_options[border_radius]',
    'type' => 'number',
    'input_attrs' => array(
        'min' => 0,
        'max' => 100,
        'step' => 1,
    ),
));

// 枠の内側の余白の設定
$wp_customize->add_setting('custom_ad_plugin_options[padding]', array(
    'default' => '0',
    'type' => 'option',
));
$wp_customize->add_control('custom_ad_plugin_padding_control', array(
    'label' => '枠の内側の余白（px）',
    'section' => 'custom_ad_plugin_section',
    'settings' => 'custom_ad_plugin_options[padding]',
    'type' => 'number',
    'input_attrs' => array(
        'min' => 0,
        'max' => 100,
        'step' => 1,
    ),
));

// 枠の外側の余白の設定
$wp_customize->add_setting('custom_ad_plugin_options[margin]', array(
    'default' => '0,0,0,0',
    'type' => 'option',
));
$wp_customize->add_control('custom_ad_plugin_margin_control', array(
    'label' => '枠外側の上,右,下,左の余白（px）',
    'section' => 'custom_ad_plugin_section',
    'settings' => 'custom_ad_plugin_options[margin]',
    'type' => 'text',
));

// 位置の設定
$wp_customize->add_setting('custom_ad_plugin_options[box_align]', array(
    'default' => 'left',
    'type' => 'option',
));
$wp_customize->add_control('custom_ad_plugin_box_align_control', array(
    'label' => '位置',
    'section' => 'custom_ad_plugin_section',
    'settings' => 'custom_ad_plugin_options[box_align]',
    'type' => 'select',
    'choices' => array(
        'left' => '左',
        'center' => '中央',
        'right' => '右',
    ),
));

// テキストの表示の設定
$wp_customize->add_setting('custom_ad_plugin_options[display]', array(
    'default' => 'on',
    'type' => 'option',
));
$wp_customize->add_control('custom_ad_plugin_display_control', array(
    'label' => 'テキストの表示',
    'section' => 'custom_ad_plugin_section',
    'settings' => 'custom_ad_plugin_options[display]',
    'type' => 'radio',
    'choices' => array(
        'on' => '表示',
        'off' => '非表示',
    ),
));


$wp_customize->add_setting('custom_ad_plugin_options[displayed_post_checkbox]', array(
    'default' => '',
    'type' => 'option',
));

$wp_customize->add_control(new Posts_Checkbox_Custom_Control($wp_customize, 'custom_ad_plugin_displayed_post_checkbox_control', array(
    'label' => '表示する記事',
    'section' => 'custom_ad_plugin_section',
    'settings' => 'custom_ad_plugin_options[displayed_post_checkbox]',
)));

class Reset_Button_Custom_Control extends WP_Customize_Control {
    public $type = 'reset-button';

    public function render_content() {
        echo '<label>';
        echo '<span class="customize-control-title">' . esc_html($this->label) . '</span>';
        echo '<input type="button" value="設定をリセット" class="button button-secondary reset-design-button">';
        echo '</label>';
    }
}

// デザインのリセットボタンのコントロールを追加
$wp_customize->add_setting('custom_ad_plugin_reset_design', array(
    'default' => '',
    'type' => 'option',
    'capability' => 'edit_theme_options',
    'sanitize_callback' => 'sanitize_text_field',
));

$wp_customize->add_control(new Reset_Button_Custom_Control($wp_customize, 'custom_ad_plugin_reset_design_control', array(
    'label' => '',
    'section' => 'custom_ad_plugin_section',
    'settings' => 'custom_ad_plugin_reset_design',
)));

}

add_action('customize_register', 'custom_ad_plugin_customize_register');

// プラグインが削除されたときに実行する関数
function custom_ad_plugin_uninstall() {
    delete_option('custom_ad_plugin_options');
}

register_uninstall_hook(__FILE__, 'custom_ad_plugin_uninstall');
