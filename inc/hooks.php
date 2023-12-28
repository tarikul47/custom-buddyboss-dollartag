<?php
// Need to functionality for deleting a comment and make sure also need to decrease course the hashtag 
add_action('bp_before_activity_delete', 'wecoder_delete_buddypress_activity_hashtag_table');

// Need to functionality for adding a new coomnent reply 
add_filter('bp_activity_comment_content', 'wecoder_activity_comment_hashtags_filter', 20, 2);

// Need to functionality for adding a new activity 
add_filter('bp_activity_new_update_content', 'wecoder_activity_hashtags_filter');
add_filter('bp_get_activity_content_body', 'wecoder_activity_hashtags_filter', 8);
add_filter('groups_activity_new_update_content', 'wecoder_activity_hashtags_filter');

add_filter('bp_blogs_activity_new_post_content', 'wecoder_activity_hashtags_filter');
add_filter('bp_blogs_activity_new_comment_content', 'wecoder_activity_hashtags_filter');

//support edit activity stream plugin
add_filter('bp_edit_activity_action_edit_content', 'wecoder_activity_hashtags_filter');


if (function_exists('buddypress') && isset(buddypress()->buddyboss)) {
    add_filter('bp_activity_get_where_conditions', 'wecoder_override_buddyboss_serach_activity_query', 10, 5);
}


/**
 * admin css js include
 */

function wecoder_enqueue_admin_scripts()
{
    if (!wp_style_is('font-awesome', 'enqueued')) {
        wp_enqueue_style('font-awesome', 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css');
    }

    if (!wp_script_is('wecoder_admin_setting_js', 'enqueued')) {

        wp_register_script(
            $handle    = 'wecoder_admin_setting_js',
            $src       = get_stylesheet_directory_uri() . '/wecoder-hashtag/assets/js/wecoder-admin-setting.js',
            $deps      = array('jquery'),
            $ver       = time(),
            $in_footer = true
        );
        wp_localize_script(
            'wecoder_admin_setting_js',
            'wecoder_ajax_obj',
            array(
                'ajax_url'        => admin_url('admin-ajax.php'),
                'activate_text'   => esc_html__('Activate', 'buddypress-hashtags'),
                'deactivate_text' => esc_html__('Deactivate', 'buddypress-hashtags'),
                'ajax_nonce'           => wp_create_nonce('wecoder_admin_setting_nonce'),
                'wait_text'  => __('Please wait', 'buddypress-hashtags'),
            )
        );
        wp_enqueue_script('wecoder_admin_setting_js');
    }

    if (!wp_style_is('wecoder-admin-setting-css', 'enqueued')) {
        wp_enqueue_style('wecoder-admin-setting-css', get_stylesheet_directory_uri() . '/wecoder-hashtag/assets/css/wecoder-admin-setting.css');
    }
}
add_action('admin_enqueue_scripts', 'wecoder_enqueue_admin_scripts');



/**
 * Register the plugins's admin menu.
 *
 * @since    1.0.0
 */
function wecoder_add_menu_buddypress_hashtags()
{
    add_menu_page(esc_html__('HashTag Option', 'wecoder'), esc_html__('HashTag Option', 'wecoder'), 'manage_options', 'wecoder', 'wecoder_hashtags_settings_page', 'dashicons-lightbulb', 59);
}

add_action('admin_menu', 'wecoder_add_menu_buddypress_hashtags');

/**
 * Our Hashtag container html
 */

function wecoder_hashtags_settings_page()
{
    // Include the template based on the tab parameter
    $tabName = isset($_GET['tab']) ? $_GET['tab'] : 'general';
    //var_dump($tabName);
?>
    <div class="wrap">
        <div class="wbcom-wrap">
            <div class="blpro-header">
                <div class="wbcom_admin_header-wrapper">
                    <div id="wb_admin_plugin_name">
                        BuddyPress Hashtags
                    </div>
                </div>
            </div>
            <div class="wbcom-admin-settings-page">
                <div class="wbcom-tabs-section">
                    <div class="nav-tab-wrapper">
                        <div class="wb-responsive-menu">
                            <span>Menu</span>
                            <input class="wb-toggle-btn" type="checkbox" id="wb-toggle-btn" />
                            <label class="wb-toggle-icon" for="wb-toggle-btn">
                                <span class="wb-icon-bars"></span>
                            </label>
                        </div>
                        <ul>
                            <li class="General">
                                <a class="nav-tab <?php echo $tabName === "general" ? ' nav-tab-active' : ''; ?>" href="admin.php?page=wecoder&amp;tab=general">General</a>
                            </li>
                            <li class="Hashtags" logs="">
                                <a class="nav-tab <?php echo $tabName === "hashtag-logs" ? ' nav-tab-active' : ''; ?>" href="admin.php?page=wecoder&amp;tab=hashtag-logs">Hashtags logs</a>
                            </li>
                        </ul>
                    </div>
                </div>
                <div class="wbcom-tab-content">

                    <?php
                    // Function to include the template based on the tab name
                    function includeTemplate($tabName)
                    {
                        switch ($tabName) {
                            case 'general':
                                include 'wecoder-setting-general-tab.php';
                                break;
                            case 'hashtag-logs':
                                include 'wecoder-hashtag-delete-tab.php';
                                break;
                                // Add cases for more tabs as needed
                            default:
                                echo 'No content available for this tab.';
                        }
                    }
                    includeTemplate($tabName);
                    ?>
                </div>
            </div>
        </div>
    </div>
<?php
}
add_action('admin_init', 'wecoder_add_admin_register_setting');


/**
 * Delete hashtag 
 */
add_action('wp_ajax_wecoder_delete_hashtag', 'wecoder_delete_hashtag');

//ajax action to clear buddypress hashtag table
add_action('wp_ajax_wecoder_clear_buddypress_hashtag_table', 'wecoder_clear_buddypress_hashtag_table');
