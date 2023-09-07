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
            wp.customize('custom_ad_plugin_options[position_align]').set('above_title_left');
            wp.customize('custom_ad_plugin_options[ad_text]').set('広告');
            wp.customize('custom_ad_plugin_options[width]').set('6');
            wp.customize('custom_ad_plugin_options[bg_color]').set('#FFFFFF');
            wp.customize('custom_ad_plugin_options[text_color]').set('#333333');
            wp.customize('custom_ad_plugin_options[border_color]').set('#333333');
            wp.customize('custom_ad_plugin_options[border_style]').set('solid');
            wp.customize('custom_ad_plugin_options[font_size]').set('14');
            wp.customize('custom_ad_plugin_options[border_width]').set('1');
            wp.customize('custom_ad_plugin_options[border_radius]').set('0');
            wp.customize('custom_ad_plugin_options[padding]').set('0');
            wp.customize('custom_ad_plugin_options[margin]').set('0,0,0,0');
            wp.customize('custom_ad_plugin_options[display]').set('on');
            wp.customize('custom_ad_plugin_options[displayed_post_checkbox]').set('');
            $('.post-checkbox').prop('checked', false);

            wp.customize.previewer.refresh(); // プレビューを更新
        }
    });
});