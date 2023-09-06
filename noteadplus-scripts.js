jQuery(document).ready(function($) {
    // カスタマイザー内のチェックボックスの状態が変更されたとき
    $(document).on('change', '.post-checkbox', function() {
        let selectedPosts = [];
        
        // 「全ての記事」のチェックボックスがチェックされた場合
        if ($(this).hasClass('all-posts-checkbox') && $(this).is(':checked')) {
            $('.post-checkbox:not(.all-posts-checkbox)').prop('checked', true);
        } else if ($(this).hasClass('all-posts-checkbox') && !$(this).is(':checked')) {
            $('.post-checkbox:not(.all-posts-checkbox)').prop('checked', false);
        }

        $('.post-checkbox:checked').each(function() {
            selectedPosts.push($(this).val());
        });
        $(this).closest('li').find('input[type="hidden"]').val(selectedPosts.join(',')).trigger('change');
    });
});

jQuery(document).ready(function($) {
    $(document).on('click', '.reset-design-button', function() {
        if (confirm('デザインの設定をリセットしますか？')) {
            // カスタマイザーの各設定をデフォルトにリセット
            wp.customize('custom_ad_plugin_options[ad_text]').set('広告'); // デフォルトの「広告」に設定をリセット
            wp.customize('custom_ad_plugin_options[width]').set('10');     // デフォルトの「10」に設定をリセット
            // 他の設定も同様にデフォルトにリセット
            wp.customize.previewer.refresh(); // プレビューを更新
        }
    });
});

function custom_ad_plugin_customizer_script() {
    wp_enqueue_script('custom-ad-plugin-customizer', plugins_url('noteadplus-scripts.js', __FILE__), array('jquery', 'customize-preview'));
}
add_action('customize_preview_init', 'custom_ad_plugin_customizer_script');
