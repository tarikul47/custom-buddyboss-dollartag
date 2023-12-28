<?php


class Wecoder_Hashtag_Widget extends WP_Widget
{

    public function __construct($id = null, $name = null, $args = null)
    {

        $id   = (null !== $id) ? $id : 'wecoder_Hashtag_Widget';
        $name = (null !== $name) ? $name : __('Wecoder Hashtags', 'wecoder-hashtag');
        $args = (null !== $args) ? $args : array('description' => __('Wecoder Hashtags Widget', 'wecoder-hashtag'));

        parent::__construct(
            $id,
            $name,
            $args
        );
    }

    public function shuffle_assoc($list)
    {

        if (!is_array($list)) {
            return $list;
        }

        $keys = array_keys($list);
        shuffle($keys);
        $random = array();
        foreach ($keys as $key) {
            $random[$key] = $list[$key];
        }
        return $random;
    }

    /**
     * Outputs the content of the widget
     *
     * @param array $args
     * @param array $instance
     * @return void
     */
    public function widget($args, $instance)
    {
        $instance = apply_filters('wecoder_bp_hashtag_widget_instance', $instance);

        if (!isset($instance['sortby'])) {
            $instance['sortby'] = 0;
        }

        if (!isset($instance['sortorder'])) {
            $instance['sortorder'] = 0;
        }

        if (!isset($instance['displaystyle'])) {
            $instance['displaystyle'] = 0;
        }

        $count = !empty($instance['count']) ? '1' : '0';
        echo $args['before_widget']; // phpcs:ignore WordPress.XSS.EscapeOutput.OutputNotEscaped
?>

        <div>
            <div>
                <?php
                if (!empty($instance['title'])) {
                    echo $args['before_title'] . apply_filters('widget_title', $instance['title']) . $args['after_title']; // phpcs:ignore WordPress.XSS.EscapeOutput.OutputNotEscaped
                }
                ?>
            </div>
            <?php

            if (!isset($instance['limit'])) {
                $instance['limit'] = 12;
            }

            $limit = $instance['limit'];

            global $wpdb;
            $table_name = $wpdb->prefix . 'wecoder_hashtags';
            $hashtags   = array();

            if ($wpdb->get_var("SHOW TABLES LIKE '$table_name'")) {

                if (3 == $instance['sortby']) {
                    $sortorder = ($instance['sortorder'] == 0) ? 'DESC' : 'ASC';
                    $sql       = "SELECT * FROM $table_name WHERE ht_type IN ('buddypress') ORDER BY ht_count DESC, ht_last_count {$sortorder} LIMIT $limit";
                } else {
                    $sql = "SELECT * FROM $table_name WHERE ht_type IN ('buddypress') ORDER BY ht_count DESC LIMIT $limit";
                }

                /**
                 * Filters the query of the widget hashtag items.
                 *
                 * @param string $sql The SQL query
                 * @param string $type The type of the requested hashtags
                 * @param int    $limit How many rows to fetch
                 */
                $query     = apply_filters('wbcom_hashtag_widget_query', $sql, 'buddypress', $limit);

                $_hashtags = $wpdb->get_results($query);

                if ($_hashtags) {
                    foreach ($_hashtags as $key => $ht_data) {
                        $hashtags[$ht_data->ht_name] = array(
                            'ht_count'      => $ht_data->ht_count,
                            'ht_last_count' => $ht_data->ht_last_count,
                            'ht_type'       => $ht_data->ht_type,
                        );
                    }
                }
            }

            $max     = 10;
            $min     = 1;
            $fontMin = 14;
            $fontMax = 28;

            if (count($hashtags)) {
                $result = array();

                if (0 == $instance['sortby']) {
                    if (0 == $instance['sortorder']) {
                        ksort($hashtags);
                    }

                    if (1 == $instance['sortorder']) {
                        krsort($hashtags);
                    }
                } elseif (1 == $instance['sortby']) {
                    if (0 == $instance['sortorder']) {
                        asort($hashtags);
                    }

                    if (1 == $instance['sortorder']) {
                        arsort($hashtags);
                    }
                } elseif (2 == $instance['sortby']) {
                    $hashtags = $this->shuffle_assoc($hashtags);
                }

                /**
                 * Final filter before the hashtags array is displayed
                 *
                 * @param array  $hashtags The hashtags array
                 * @param string $type The type of the requested hashtags
                 * @param int    $limit How many rows to fetch
                 */
                $hashtags = apply_filters(
                    'wbcom_hashtag_widget_array_hashtags',
                    $hashtags,
                    'buddypress',
                    $limit
                );

                $wrapper = '';

                if (1 == $instance['displaystyle']) {
                    $wrapper = 'wecoder-hashtags-wrapper-list';
                } elseif (0 == $instance['displaystyle']) {
                    $wrapper = 'wecoder-hashtags-wrapper-cloud';
                }
            ?>
                <div>
                    <div class="wecoder-widget--hashtags">
                        <div class="wecoder-hashtags-wrapper <?php echo esc_attr($wrapper); ?>">

                            <?php
                            foreach ($hashtags as $name => $hash_data) {

                                $percentage = 100; // default percentage if tags have no counts (the max is 0)
                                if ($max > 0) {
                                    $percentage = round($hash_data['ht_count'] / $max * 10) * 10;
                                }

                                $size = 'wecoder-hashtag--box wecoder-hashtag--size' . $percentage;

                                if (1 == $instance['displaystyle']) {
                                    $size = '';
                                }

                                if (2 == $instance['displaystyle']) {
                                    $size = 'wecoder-hashtag--size' . $percentage;
                                }
                                $style = '';
                                if ($instance['sortby'] == 2) {
                                    $fontsize = ($hash_data['ht_count'] == $min) ? $fontMin : ($hash_data['ht_count'] / $max) * ($fontMax - $fontMin) + $fontMin;
                                    $style    = 'style=font-size:' . $fontsize . 'px;';
                                }

                                $activity_url = site_url(bp_get_activity_root_slug());
                                echo '<div data-size="' . esc_attr($size) . '" ' . esc_attr($style) . '>';
                                echo ' <a href="' . esc_url($activity_url) . '/?activity_search=$' . esc_html(htmlspecialchars($name)) . '" rel="nofollow" class="hashtag" id="' . esc_attr(htmlspecialchars($name)) . '">$' . esc_html(htmlspecialchars($name));
                                echo ($count == 1) ? ' (' . $hash_data['ht_count'] . ')' : ''; // phpcs:ignore WordPress.XSS.EscapeOutput.OutputNotEscaped

                                echo '</a>';
                                echo '</div>';
                            }
                            ?>
                        </div>
                    </div>
                </div>
            <?php } else { ?>
                <div>
                    <span class='wecoder-text--muted'><?php echo esc_html_e('No hashtags', 'wecoder-hashtag'); ?></span>
                </div>
            <?php } ?>
        </div>
    <?php
        echo $args['after_widget']; // phpcs:ignore WordPress.XSS.EscapeOutput.OutputNotEscaped
    }

    /**
     * Outputs the admin options form
     *
     * @param array $instance The widget options
     */
    public function form($instance)
    {

        $limit_options = array();

        for ($i = 1; $i <= 50; $i++) {
            $limit_options[] = $i;
        }

        $instance['fields'] = array(
            // general
            'limit'         => true,
            'limit_options' => $limit_options,
            'title'         => true,
            'integrated'    => false,
            'position'      => false,
            'hideempty'     => false,
        );

        if (!isset($instance['title'])) {
            $instance['title'] = __('Community Hashtags', 'wecoder-hashtag');
        }

        $this->instance = $instance;

        $settings = apply_filters(
            'wecoder_bp_hashtag_widget_form',
            array(
                'html'     => '',
                'that'     => $this,
                'instance' => $instance,
            )
        );
        echo $settings['html']; // phpcs:ignore WordPress.XSS.EscapeOutput.OutputNotEscaped

        $title        = !empty($instance['title']) ? $instance['title'] : 'Wecoder Hashtags';
        $limit        = !empty($instance['limit']) ? $instance['limit'] : 20;
        $sortby       = (isset($instance['sortby']) && ($instance['sortby'] != '' || $instance['sortby'] == 0)) ? $instance['sortby'] : 2;
        $sortorder    = !empty($instance['sortorder']) ? $instance['sortorder'] : 0;
        $displaystyle = !empty($instance['displaystyle']) ? $instance['displaystyle'] : 0;
        $count        = isset($instance['count']) ? (bool) $instance['count'] : false;
    ?>

        <p>
            <label for="<?php echo esc_attr($this->get_field_name('title')); ?>"><?php esc_html_e('Title:', 'wecoder-hashtag'); ?></label>
            <input id="<?php echo esc_attr($this->get_field_id('title')); ?>" name="<?php echo esc_attr($this->get_field_name('title')); ?>" type="text" value="<?php echo esc_attr($title); ?>">
        </p>
        <p>
            <label for="<?php echo esc_attr($this->get_field_id('limit')); ?>"><?php esc_html_e('Limit:', 'wecoder-hashtag'); ?></label>
            <select class="widefat" id="<?php echo esc_attr($this->get_field_id('limit')); ?>" name="<?php echo esc_attr($this->get_field_name('limit')); ?>">
                <?php
                $options = array();
                for ($i = 1; $i <= 100; $i++) {
                    if ($i <= 10 || $i % 2 == 0) {
                        $options[] = $i;
                    }
                }

                if (!empty($instance['fields']['limit_options'])) {
                    $options = $instance['fields']['limit_options'];
                }

                foreach ($options as $option) {
                ?>
                    <option value="<?php echo esc_attr($option); ?>" <?php
                                                                        if ($option == $limit) {
                                                                            echo ' selected ';
                                                                        }
                                                                        ?>><?php echo esc_html($option); ?></option>
                <?php
                }
                ?>
            </select>
        </p>
        <p>
            <select name="<?php echo esc_attr($this->get_field_name('displaystyle')); ?>" id="<?php echo esc_attr($this->get_field_id('displaystyle')); ?>">
                <option value="0" <?php
                                    if (0 === $displaystyle) {
                                        echo ' selected ';
                                    }
                                    ?>><?php echo esc_html_e('Cloud', 'wecoder-hashtag'); ?></option>
                <option value="1" <?php
                                    if (1 === $displaystyle) {
                                        echo ' selected ';
                                    }
                                    ?>><?php echo esc_html_e('List', 'wecoder-hashtag'); ?></option>
            </select>

            <select name="<?php echo esc_attr($this->get_field_name('sortby')); ?>" id="<?php echo esc_attr($this->get_field_id('sortby')); ?>">
                <option value="0" <?php
                                    if (0 === $sortby) {
                                        echo ' selected ';
                                    }
                                    ?>><?php echo esc_html_e('Sorted by name', 'wecoder-hashtag'); ?></option>
                <option value="1" <?php
                                    if (1 === $sortby) {
                                        echo ' selected ';
                                    }
                                    ?>><?php echo esc_html_e('Sorted by size', 'wecoder-hashtag'); ?></option>
                <option value="3" <?php
                                    if (3 === $sortby) {
                                        echo ' selected ';
                                    }
                                    ?>><?php echo esc_html_e('Sorted by date', 'wecoder-hashtag'); ?></option>
                <option value="2" <?php
                                    if (2 === $sortby) {
                                        echo ' selected ';
                                    }
                                    ?>><?php echo esc_html_e('Random', 'wecoder-hashtag'); ?></option>
            </select>

            <select name="<?php echo esc_attr($this->get_field_name('sortorder')); ?>" id="<?php echo esc_attr($this->get_field_id('sortorder')); ?>">
                <option value="0" <?php
                                    if (0 === $sortorder) {
                                        echo ' selected ';
                                    }
                                    ?>><?php echo esc_html_e('&uarr;', 'wecoder-hashtag'); ?></option>
                <option value="1" <?php
                                    if (1 === $sortorder) {
                                        echo ' selected ';
                                    }
                                    ?>><?php echo esc_html_e('&darr;', 'wecoder-hashtag'); ?></option>
            </select>
        </p>
        <p>
            <input type="checkbox" class="checkbox" id="<?php echo esc_attr($this->get_field_id('count')); ?>" name="<?php echo esc_attr($this->get_field_name('count')); ?>" <?php checked($count); ?> />
            <label for="<?php echo esc_attr($this->get_field_id('count')); ?>"><?php esc_html_e('Show hashtag counts', 'wecoder-hashtag'); ?></label>
        </p>
<?php
    }

    /**
     * Sanitize widget form values as they are saved.
     *
     * @see WP_Widget::update()
     * @param array $new_instance Values just sent to be saved.
     * @param array $old_instance Previously saved values from database.
     *
     * @return array Updated safe values to be saved.
     */
    public function update($new_instance, $old_instance)
    {
        $instance          = array();
        $instance['title'] = (!empty($new_instance['title'])) ? strip_tags($new_instance['title']) : '';
        $instance['limit'] = (int) $new_instance['limit'];

        $instance['sortby']       = (int) $new_instance['sortby'];
        $instance['sortorder']    = (int) $new_instance['sortorder'];
        $instance['displaystyle'] = (int) $new_instance['displaystyle'];
        $instance['count']        = !empty($new_instance['count']) ? 1 : 0;
        return $instance;
    }
}
