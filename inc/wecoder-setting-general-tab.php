<?php
$wecoder_general_settings = get_option('wecoder_general_settings');
?>
<div class="wbcom-wrapper-admin">
    <div class="wbcom-admin-title-section">
        <h3>Hashtags General Setting</h3>
    </div>
    <div class="wbcom-admin-option-wrap wbcom-admin-option-wrap-view">
        <form method="post" action="options.php">
            <?php
            settings_fields('wecoder_general_settings_section');
            do_settings_sections('wecoder_general_settings_section');
            ?>
            <div class="form-table">
                <div class="wbcom-settings-section-wrap bpht-lengths-row" style="">
                    <div class="wbcom-settings-section-options-heading">
                        <label for="blogname">Minimum hashtag length</label>
                        <p class="description" id="tagline-description">Default value is 3.</p>
                    </div>
                    <div class="wbcom-settings-section-options">
                        <input name="wecoder_general_settings[min_length]" type="number" min="3" class="regular-text" value="<?php echo (isset($wecoder_general_settings['min_length']) && $wecoder_general_settings['min_length']) ? esc_attr($wecoder_general_settings['min_length']) : '3'; ?>" placeholder="set minimum hashtag length">
                    </div>
                </div>
                <div class="wbcom-settings-section-wrap bpht-lengths-row" style="">
                    <div class="wbcom-settings-section-options-heading">
                        <label for="blogname">Maximum hashtag length</label>
                        <p class="description" id="tagline-description">Default value is 16.</p>
                    </div>
                    <div class="wbcom-settings-section-options">
                        <input name="wecoder_general_settings[max_length]" type="number" min="5" class="regular-text" value="<?php echo (isset($wecoder_general_settings['max_length']) && $wecoder_general_settings['max_length']) ? esc_attr($wecoder_general_settings['max_length']) : '16'; ?>" placeholder="set maximum hashtag length">
                    </div>
                </div>
                <div class="wbcom-settings-section-wrap">
                    <div class="wbcom-settings-section-options-heading">
                        <label for="blogname">Clear buddypress widgets hashtags</label>
                        <p class="description" id="tagline-description">This will only clear old hashtags from buddypress community widget.</p>
                    </div>
                    <div class="wbcom-settings-section-options">
                        <a href="javascript:void(0)" class="wecoder-clear-bp-hashtags button button-primary">Clear</a>
                    </div>
                </div>
            </div>
            <p class="submit">
                <input type="submit" name="submit" id="submit" class="button button-primary" value="Save Changes">
            </p>
        </form>
    </div>
</div>