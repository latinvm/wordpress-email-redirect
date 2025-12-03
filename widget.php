<?php

class Email_Redirect_Widget extends WP_Widget {

    public function __construct() {
        parent::__construct(
            'email_redirect_widget',
            __('Email Redirect Form', 'email-redirect'),
            array('description' => __('Email form that redirects users based on their email domain', 'email-redirect'))
        );
    }

    public function widget($args, $instance) {
        echo $args['before_widget'];

        if (!empty($instance['title'])) {
            echo $args['before_title'] . apply_filters('widget_title', $instance['title']) . $args['after_title'];
        }

        $unique_id = uniqid('er-form-');
        ?>
        <div class="er-widget-container">
            <form class="er-email-form" id="<?php echo esc_attr($unique_id); ?>" data-form-id="<?php echo esc_attr($unique_id); ?>">
                <div class="er-form-group">
                    <label for="<?php echo esc_attr($unique_id); ?>-email"><?php esc_html_e('Email Address:', 'email-redirect'); ?></label>
                    <input type="email"
                           id="<?php echo esc_attr($unique_id); ?>-email"
                           name="email"
                           required
                           placeholder="<?php esc_attr_e('your.email@company.com', 'email-redirect'); ?>" />
                </div>
                <button type="submit" class="er-submit-btn"><?php esc_html_e('Submit', 'email-redirect'); ?></button>
                <div class="er-message" style="display:none;"></div>
            </form>
        </div>
        <?php

        echo $args['after_widget'];
    }

    public function form($instance) {
        $title = !empty($instance['title']) ? $instance['title'] : __('Enter Your Email', 'email-redirect');
        ?>
        <p>
            <label for="<?php echo esc_attr($this->get_field_id('title')); ?>"><?php esc_html_e('Title:', 'email-redirect'); ?></label>
            <input class="widefat"
                   id="<?php echo esc_attr($this->get_field_id('title')); ?>"
                   name="<?php echo esc_attr($this->get_field_name('title')); ?>"
                   type="text"
                   value="<?php echo esc_attr($title); ?>">
        </p>
        <?php
    }

    public function update($new_instance, $old_instance) {
        $instance = array();
        $instance['title'] = (!empty($new_instance['title'])) ? sanitize_text_field($new_instance['title']) : '';
        return $instance;
    }
}
