<?php

include_once "inc/hooks.php";
include_once "inc/functions.php";

include_once('widget/hashtag-widget.php');
register_widget('Wecoder_Hashtag_Widget');


/**
 * 1. New Activity content add and modify 
 */

function wecoder_activity_hashtags_filter($content)
{
    global $bp;
    $wecoder_general_settings = get_option('wecoder_general_settings');
   // var_dump($wecoder_general_settings);

    $minlen                = (isset($wecoder_general_settings['min_length']) && $wecoder_general_settings['min_length']) ? $wecoder_general_settings['min_length'] : 3;
    $maxlen                = (isset($wecoder_general_settings['max_length']) && $wecoder_general_settings['max_length']) ? $wecoder_general_settings['max_length'] : 16;

    $pattern = '/[$]([\p{L}_0-9a-zA-Z-]{' . $minlen . ',' . $maxlen . '})/iu';

    //  $an_enabled = wecoder_alpha_numeric_hashtags_enabled();

    // if ($an_enabled) {
    //     // $pattern = " /#(\S{1,})/u";
    //     $pattern = ' /(?<!\S)#(\S{1,})/u';
    //     $content = str_replace(array('<p>', '</p>'), array('<p> ', ' </p>'), $content);
    // }

    // $hashtags_option = get_option('wecoder_hashtags');

    $old_activity_url = trailingslashit(get_bloginfo('url')) . BP_ACTIVITY_SLUG;

    $activity_url     = site_url(bp_get_activity_root_slug());

    $hashtags         = array();
    preg_match_all($pattern, $content, $hashtags);

    if ($hashtags) {
        if (!$hashtags = array_unique($hashtags[1])) {
            return $content;
        }

        add_filter('bp_bypass_check_for_moderation', '__return_true');

        foreach ((array) $hashtags as $hashtag) {

            //   $pattern = "/(^|\s|\b)#" . $hashtag . "($|\b)/";

            $pattern = '/[$]([\p{L}_0-9a-zA-Z-]{' . $minlen . ',' . $maxlen . '})/iu';


            // if ($an_enabled) {
            //     $pattern = '/#' . $hashtag . '/u';
            // }

            $replacement = '<a href="' . $activity_url . '/?activity_search=' . urlencode("$" . $hashtag) . '">$' . htmlspecialchars($hashtag) . '</a>';

            $content = preg_replace($pattern, $replacement, str_replace('<p>', '<p> ', $content));

            $old_url = $old_activity_url . '/?s=%24' . htmlspecialchars($hashtag);
            $new_url = $activity_url . '/?activity_search=%24' . htmlspecialchars($hashtag);
            $content = str_replace(array($old_url, '?s='), array($new_url, '?s='), $content);

            if (current_action() == 'bp_activity_new_update_content' || current_action() == 'groups_activity_new_update_content') {

                wecoder_db_buddypress_hashtag_entry($hashtag, 'buddypress');
            }
        }
    }

    return $content;
}

/**
 *  2. Need to functionality for adding a new activity 
 */

function wecoder_activity_comment_hashtags_filter($content, $type)
{
    global $bp;
    //  $wecoder_general_settings = get_option('wecoder_general_settings');
    $minlen                = (isset($wecoder_general_settings['min_length']) && $wecoder_general_settings['min_length']) ? $wecoder_general_settings['min_length'] : 3;
    $maxlen                = (isset($wecoder_general_settings['max_length']) && $wecoder_general_settings['max_length']) ? $wecoder_general_settings['max_length'] : 16;

    $pattern = '/[$]([\p{L}_0-9a-zA-Z-]{' . $minlen . ',' . $maxlen . '})/iu';

    //   $an_enabled = wecoder_alpha_numeric_hashtags_enabled();

    // if ($an_enabled) {
    //     // $pattern = " /#(\S{1,})/u";
    //     $pattern = ' /(?<!\S)#(\S{1,})/u';
    //     $content = str_replace(array('<p>', '</p>'), array('<p> ', ' </p>'), $content);
    // }

    //   $hashtags_option = get_option('wecoder_hashtags');

    $old_activity_url = trailingslashit(get_bloginfo('url')) . BP_ACTIVITY_SLUG;
    $activity_url     = site_url(bp_get_activity_root_slug());
    $hashtags         = array();
    preg_match_all($pattern, $content, $hashtags);

    if ($hashtags) {
        if (!$hashtags = array_unique($hashtags[1])) {
            return $content;
        }

        add_filter('bp_bypass_check_for_moderation', '__return_true');

        foreach ((array) $hashtags as $hashtag) {

            $pattern = '/[$]([\p{L}_0-9a-zA-Z-]{' . $minlen . ',' . $maxlen . '})/iu';

            // if ($an_enabled) {
            //     $pattern = '/#' . $hashtag . '/u';
            // }

            $replacement = '<a href="' . $activity_url . '/?activity_search=' . urlencode("$" . $hashtag) . '">$' . htmlspecialchars($hashtag) . '</a>';

            $content = preg_replace($pattern, $replacement, str_replace('<p>', '<p> ', $content));

            $old_url = $old_activity_url . '/?s=%24' . htmlspecialchars($hashtag);
            $new_url = $activity_url . '/?activity_search=%24' . htmlspecialchars($hashtag);
            $content = str_replace(array($old_url, '?s='), array($new_url, '?s='), $content);


            if ($type == 'new' && current_action() == 'bp_activity_comment_content') {

                wecoder_db_buddypress_hashtag_entry($hashtag, 'buddypress');
            }
        }
    }

    return $content;
}

/*
* Delete Activity hashtag count when delete activity
*
*/
function wecoder_delete_buddypress_activity_hashtag_table($args)
{
    global $wpdb;

    if (isset($args['id']) && $args['id'] != '') {

        $activity_id      = $args['id'];
        $activity_content = $wpdb->get_results("SELECT content FROM {$wpdb->prefix}bp_activity  WHERE id=" . $activity_id);

        /* Get Deleted Activity Content*/
        if (!empty($activity_content)) {
            foreach ($activity_content as $content) {
                /*  Search hashtag in activity content*/

                $pattern = '/[$]([\p{L}_0-9a-zA-Z-]{3,16})/iu';

                preg_match_all($pattern, $content->content, $matches);

                if (!empty($matches[1])) {
                    foreach ($matches[1] as $hashtag) {

                        $hashtag = str_replace(['</a>', '<br'], [''], wp_strip_all_tags($hashtag));

                  //      error_log(print_r($hashtag, true));

                        /* Check hashtag in hashtag table */
                        $hashtags_count = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}wecoder_hashtags  WHERE ht_type = 'buddypress' AND ht_name='" . $hashtag . "'");

                        error_log(print_r($hashtags_count, true));

                     //   exit;


                        if (!empty($hashtags_count)) {
                            foreach ($hashtags_count as $value) {

                                /* If count 1 then delete hashtag from table */
                                if ($value->ht_count == 1) {
                                    $wpdb->get_results("DELETE FROM {$wpdb->prefix}wecoder_hashtags  WHERE ht_type = 'buddypress' AND ht_name='" . $hashtag . "' AND ht_id=" . $value->ht_id);
                                } else {
                                    /* More then one count then reduced hashtag count */
                                    $wpdb->get_results("UPDATE  {$wpdb->prefix}wecoder_hashtags SET ht_count = ht_count - 1  WHERE ht_type = 'buddypress' AND ht_name='" . $hashtag . "' AND ht_id=" . $value->ht_id);
                                }
                            }
                        }
                    }
                }
            }
        }
    }
}



/**
 * Serach query override for buddyboss
 *
 * @param  array $where_conditions activity search query.
 * @param  mixed $r search parameter.
 * @param  mixed $select_sql search activity.
 * @param  mixed $from_sql query.
 * @param  mixed $join_sql query.
 * @return array $where_conditions
 */

function wecoder_override_buddyboss_serach_activity_query($where_conditions, $r, $select_sql, $from_sql, $join_sql)
{
    global $wpdb;
    $search_terms_like              = '%' . bp_esc_like($r['search_terms']) . '%';
    $where_conditions['search_sql'] = $wpdb->prepare('a.content LIKE %s', $search_terms_like);
    return $where_conditions;
}
