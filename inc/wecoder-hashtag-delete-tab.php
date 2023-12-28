<?php

/**
 *
 * This file is called for general settings section at admin settings.
 */

// Exit if accessed directly.
if (!defined('ABSPATH')) {
    exit;
}
global $wpdb;
$table_name = $wpdb->prefix . 'wecoder_hashtags';
$search_tags = filter_input(INPUT_GET, 'search-hashtag') ? filter_input(INPUT_GET, 'search-hashtag') : '';
$where_search = "Where ht_type='buddypress' ";
if (isset($_GET['search-hashtag']) && $_GET['search-hashtag'] != '') {
    $where_search .= " AND ht_name like '%" . $_GET['search-hashtag'] . "%'";
}

$customPagHTML      = '';
$total_query        = "SELECT count(*) as count FROM $table_name {$where_search}";
$total              = $wpdb->get_var($total_query);
$items_per_page     = 5;
$page               = (isset($_GET['cpage'])) ? abs((int) $_GET['cpage']) : 1;
$offset             = ($page * $items_per_page) - $items_per_page;
$_hashtags          = $wpdb->get_results("SELECT * FROM $table_name {$where_search} ORDER BY ht_count DESC LIMIT {$offset}, {$items_per_page}");
$total_page         = ceil($total / $items_per_page);
?>

<div class="wbcom-wrapper-admin">
    <div class="wbcom-admin-title-section">
        <h3><?php esc_html_e('Hashtags Logs', 'buddypress-hashtags'); ?></h3>
    </div>
    <div class="wbcom-admin-option-wrap wbcom-admin-option-wrap-view">
        <form class="hashtags-logs-search" action="" method='get'>
        <?php
           // settings_fields('wecoder_general_settings_section');
           // do_settings_sections('wecoder_general_settings_section');
            ?>
            <div class="form-table">
                <div class="wbcom-settings-section-wrap">
                    <div class="wbcom-settings-section-options">
                        <input type="hidden" name="page" value='wecoder' />
                        <input type="hidden" name="tab" value='hashtag-logs' />
                        <p class="search-box hashtags-search-box">
                            <label class="screen-reader-text" for="post-search-input"><?php esc_html_e('Search hashtags:', 'buddypress-hashtags'); ?></label>
                            <input type="search" id="post-search-input" name="search-hashtag" value="<?php echo (isset($_GET['search-hashtag']) && $_GET['search-hashtag'] != '') ? $_GET['search-hashtag'] : ''; ?>">
                            <input type="submit" id="search-submit" class="button" value="<?php esc_html_e('Search hashtags', 'buddypress-hashtags'); ?>">
                        </p>
                    </div>
                </div>
                <p class="warning-msg">
                    <strong>** Warning: Deletion of Hashtag will also delete those BuddyPress activities which contain corresponding Hashtag.</strong>
                </p>
                <table class="form-table hashtags-logs-table widefat table-view-list">
                    <thead>
                        <tr>
                            <td><?php esc_html_e('Hashtag', 'buddypress-hashtags'); ?></td>
                            <td><?php esc_html_e('Count', 'buddypress-hashtags'); ?></td>
                            <td><?php esc_html_e('Type', 'buddypress-hashtags'); ?></td>
                            <td><?php esc_html_e('Action', 'buddypress-hashtags'); ?></td>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        if (!empty($_hashtags)) {
                            foreach ($_hashtags as $key => $ht_data) :
                        ?>
                                <tr id="buddypress-hashtags-<?php echo esc_attr($ht_data->ht_id); ?>">
                                    <td data-label="Hashtag"><?php echo $ht_data->ht_name; ?></td>
                                    <td data-label="Count"><?php echo $ht_data->ht_count; ?></td>
                                    <td data-label="Type"><?php echo $ht_data->ht_type; ?></td>
                                    <td data-label="Action">
                                        <a href="javascript:void(0);" class="hashtag-delete" data-id="<?php echo $ht_data->ht_id; ?>" data-name="<?php echo $ht_data->ht_name; ?>" data-type="<?php echo $ht_data->ht_type; ?>">
                                            <?php esc_html_e('Delete', 'buddypress-hashtags'); ?>
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php } else { ?>
                            <tr>
                                <td colspan="4"><?php esc_html_e('No hashtags found', 'buddypress-hashtags'); ?> </td>
                            </tr>
                        <?php } ?>
                    </tbody>
                </table>
                <?php if ($total_page > 1) { ?>
                    <div class="hashtags-pagination pagination-bottom">
                        <?php
                        $big = 999999999; // need an unlikely integer
                        echo paginate_links(
                            array(
                                'base' => add_query_arg('cpage', '%#%'),
                                'format' => '',
                                'current' => $page,
                                'total' => $total_page,
                            )
                        );
                        ?>
                    </div>
                <?php } ?>
            </div>
        </form>
    </div>
</div>