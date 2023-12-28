<?php

/**
 * Theme loaded and create two database table 
 */

function wecoder_create_hashtag_table()
{
    global $wpdb;

    $table_name = $wpdb->prefix . 'wecoder_hashtags';

    $wecoder_charset = $wpdb->get_charset_collate();

    if ($wpdb->get_var("SHOW TABLES LIKE '$table_name'") != $table_name) {
        $wecoder_sql = "CREATE TABLE $table_name (ht_id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,ht_name varchar(128),ht_type varchar(28),ht_count bigint(20) UNSIGNED NULL DEFAULT '0',ht_last_count TIMESTAMP DEFAULT CURRENT_TIMESTAMP,PRIMARY KEY (ht_id),UNIQUE INDEX ( `ht_name`, `ht_type` )) $wecoder_charset;";
        require_once ABSPATH . 'wp-admin/includes/upgrade.php';
        dbDelta($wecoder_sql);
    }

    $hashtags_items_table_name = $wpdb->prefix . 'wecoder_hashtags_items ';

    if ($wpdb->get_var("SHOW TABLES LIKE '$hashtags_items_table_name'") != $hashtags_items_table_name) {
        $wecoder_sql = "CREATE TABLE $hashtags_items_table_name (
			id int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
			user_id  bigint(20),
			item_id bigint(20) UNSIGNED NULL DEFAULT '0',
			type varchar(255),
			hashtag_items  varchar(255),
			created_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
			PRIMARY KEY (id)) $wecoder_charset;";
        require_once ABSPATH . 'wp-admin/includes/upgrade.php';
        dbDelta($wecoder_sql);
    }
}

add_action('init', 'wecoder_create_hashtag_table');


/**
 * New hash tag added 
 */

function wecoder_db_buddypress_hashtag_entry($ht_name, $ht_type, $post_id = '0')
{
    global $wpdb;
    $table_name = $wpdb->prefix . 'wecoder_hashtags';
    $hashtags_items_table_name = $wpdb->prefix . 'wecoder_hashtags_items';


    /* for buddypress hashtags */
    if ($wpdb->get_var("SHOW TABLES LIKE '$table_name'") == $table_name) {
        error_log('jjj');

        $hashtags_items_check = $wpdb->get_results("SELECT * FROM $hashtags_items_table_name WHERE hashtag_items IN ('$ht_name') AND type IN ('$ht_type') AND item_id = {$post_id}");

        if (empty($hashtags_items_check)) {

            if ($post_id != '') {
                $result = $wpdb->insert(
                    $hashtags_items_table_name,
                    array(
                        'user_id' => get_current_user_id(),
                        'item_id' => $post_id,
                        'type' => $ht_type,
                        'hashtag_items' => $ht_name,
                        'created_date' => current_time('mysql'),
                    )
                );

                error_log(print_r($result, true));
            }
        }

        $check = $wpdb->get_results("SELECT * FROM $table_name WHERE ht_name IN ('$ht_name') AND ht_type IN ('$ht_type') ");
        if (!$check) {
            $wpdb->insert(
                $table_name,
                array(
                    'ht_name' => $ht_name,
                    'ht_type' => $ht_type,
                    'ht_count' => 1,
                    'ht_last_count' => current_time('mysql'),
                )
            );
        } else {
            $wpdb->query($wpdb->prepare("UPDATE $table_name SET ht_count = ht_count + 1, ht_last_count = '%s' WHERE ht_name IN ('%s') AND ht_type IN ('%s')", current_time('mysql'), $ht_name, $ht_type));
        }



        /**
         * Fires after the hashtag has been inserted or updated.
         *
         * @param string $ht_name The hashtag.
         * @param string $ht_type The type of the hashtag.
         */
        do_action('budypress_hashtag_inserted', $ht_name, $ht_type);
    }
}

/**
 * Register settings 
 */
function wecoder_add_admin_register_setting()
{
    register_setting('wecoder_general_settings_section', 'wecoder_general_settings');
}


function wecoder_delete_hashtag()
{
    if (isset($_POST['action']) && $_POST['action'] == 'wecoder_delete_hashtag') {

        check_ajax_referer('wecoder_admin_setting_nonce', 'ajax_nonce');

        global $wpdb;

        $r = array(
            'per_page'          => 0,
            'page'              => 0,
            'search_terms'      => '$' . trim($_POST['name']), //phpcs:ignore
            'update_meta_cache' => false,
        );

        if (bp_has_activities($r)) {
            while (bp_activities()) {
                bp_the_activity();
                bp_activity_delete(array('id' => bp_get_activity_id()));
            }
        }
    }

    wp_die();
}


/**
 * clear hashtag 
 */

function wecoder_clear_buddypress_hashtag_table()
{
    global $wpdb;
    if (isset($_POST['action']) && $_POST['action'] == 'wecoder_clear_buddypress_hashtag_table') {
        check_ajax_referer('wecoder_admin_setting_nonce', 'ajax_nonce');

        $table_name = $wpdb->prefix . 'wecoder_hashtags';

        if ($wpdb->get_var("SHOW TABLES LIKE '$table_name'") == $table_name) {
            $delete = $wpdb->query("DELETE FROM $table_name WHERE ht_type = 'buddypress'");
        }
        exit();
    }
}
