<?php

/**
 *
 * This template file is used for fetching desired options page file at admin settings end.
 */

// Exit if accessed directly.
if (!defined('ABSPATH')) {
    exit;
}
$admin_tabs = filter_input(INPUT_GET, 'tab') ? filter_input(INPUT_GET, 'tab') : 'general';

if (isset($_GET['tab'])) {
    $wecoder_tab = sanitize_text_field($admin_tabs);
} else {
    $wecoder_tab = 'general';
}

bpht_include_admin_setting_tabs($wecoder_tab);

/**
 * Include setting template.
 *
 * @param string $bpht_tab
 */
function bpht_include_admin_setting_tabs($wecoder_tab)
{
    switch ($wecoder_tab) {
        case 'general':
            include 'wecoder-setting-general-tab.php';
            break;
        case 'hashtag-logs':
            include 'wecoder-hashtag-delete-tab.php';
            break;
    }
}
