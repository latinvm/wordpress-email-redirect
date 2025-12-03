<?php
/**
 * Plugin Name: Email Redirect
 * Description: Redirects users to URLs based on their email domain
 * Version: 1.0.0
 * Author: Roy Boverhof
 * Text Domain: email-redirect
 * Domain Path: /languages
 * License: GPL-2.0-or-later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 */

if (!defined('ABSPATH')) {
    exit;
}

class Email_Redirect {

    private $option_name = 'er_domain_mappings';

    public function __construct() {
        // Load text domain
        add_action('plugins_loaded', array($this, 'load_textdomain'));

        // Admin menu
        add_action('admin_menu', array($this, 'add_admin_menu'));
        add_action('admin_init', array($this, 'register_settings'));

        // Admin scripts
        add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_scripts'));

        // Register widget
        add_action('widgets_init', array($this, 'register_widget'));

        // AJAX handler
        add_action('wp_ajax_er_process_email', array($this, 'process_email'));
        add_action('wp_ajax_nopriv_er_process_email', array($this, 'process_email'));

        // Enqueue scripts
        add_action('wp_enqueue_scripts', array($this, 'enqueue_scripts'));

        // Register shortcode
        add_shortcode('email_redirect_form', array($this, 'render_shortcode'));
    }

    public function load_textdomain() {
        load_plugin_textdomain(
            'email-redirect',
            false,
            dirname(plugin_basename(__FILE__)) . '/languages'
        );
    }

    public function add_admin_menu() {
        add_options_page(
            __('Email Redirect Settings', 'email-redirect'),
            __('Email Redirect', 'email-redirect'),
            'manage_options',
            'email-redirect',
            array($this, 'render_settings_page')
        );
    }

    public function register_settings() {
        register_setting('er_settings', $this->option_name, array($this, 'sanitize_mappings'));
    }

    public function sanitize_mappings($input) {
        if (!is_array($input)) {
            return array();
        }

        $sanitized = array();
        foreach ($input as $mapping) {
            if (!empty($mapping['domain']) && !empty($mapping['url'])) {
                $sanitized[] = array(
                    'domain' => sanitize_text_field($mapping['domain']),
                    'url' => esc_url_raw($mapping['url'])
                );
            }
        }
        return $sanitized;
    }

    public function enqueue_admin_scripts($hook) {
        if ($hook !== 'settings_page_email-redirect') {
            return;
        }

        $mappings = get_option($this->option_name, array());

        wp_enqueue_script(
            'er-admin-script',
            plugin_dir_url(__FILE__) . 'admin-script.js',
            array('jquery'),
            '1.0.0',
            true
        );

        wp_localize_script('er-admin-script', 'erAdmin', array(
            'rowCount' => count($mappings),
            'optionName' => $this->option_name,
            'placeholderDomain' => esc_attr__('e.g., company.com', 'email-redirect'),
            'placeholderUrl' => esc_attr__('https://example.com/page', 'email-redirect'),
            'removeText' => esc_html__('Remove', 'email-redirect')
        ));

        wp_enqueue_style(
            'er-admin-style',
            plugin_dir_url(__FILE__) . 'admin-style.css',
            array(),
            '1.0.0'
        );
    }

    public function render_settings_page() {
        if (!current_user_can('manage_options')) {
            return;
        }

        $mappings = get_option($this->option_name, array());
        ?>
        <div class="wrap">
            <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
            <form method="post" action="options.php">
                <?php
                settings_fields('er_settings');
                ?>
                <table class="wp-list-table widefat fixed striped" id="er-mappings-table">
                    <thead>
                        <tr>
                            <th style="width: 40%;"><?php esc_html_e('Domain', 'email-redirect'); ?></th>
                            <th style="width: 50%;"><?php esc_html_e('Redirect URL', 'email-redirect'); ?></th>
                            <th style="width: 10%;"><?php esc_html_e('Action', 'email-redirect'); ?></th>
                        </tr>
                    </thead>
                    <tbody id="er-mappings-body">
                        <?php
                        if (!empty($mappings)) {
                            foreach ($mappings as $index => $mapping) {
                                $this->render_mapping_row($index, $mapping);
                            }
                        } else {
                            $this->render_mapping_row(0, array('domain' => '', 'url' => ''));
                        }
                        ?>
                    </tbody>
                </table>
                <p>
                    <button type="button" class="button" id="er-add-row"><?php esc_html_e('Add Mapping', 'email-redirect'); ?></button>
                </p>
                <p class="description">
                    <?php esc_html_e('Enter domains without protocol (e.g., company.com or mail.company.com). Subdomain configurations take precedence over main domain matches.', 'email-redirect'); ?>
                </p>
                <?php submit_button(); ?>
            </form>
        </div>
        <?php
    }

    private function render_mapping_row($index, $mapping) {
        ?>
        <tr>
            <td>
                <input type="text"
                       name="<?php echo esc_attr($this->option_name); ?>[<?php echo esc_attr($index); ?>][domain]"
                       value="<?php echo esc_attr($mapping['domain']); ?>"
                       class="regular-text"
                       placeholder="<?php esc_attr_e('e.g., company.com', 'email-redirect'); ?>" />
            </td>
            <td>
                <input type="url"
                       name="<?php echo esc_attr($this->option_name); ?>[<?php echo esc_attr($index); ?>][url]"
                       value="<?php echo esc_url($mapping['url']); ?>"
                       class="regular-text"
                       placeholder="<?php esc_attr_e('https://example.com/page', 'email-redirect'); ?>" />
            </td>
            <td>
                <button type="button" class="button er-remove-row"><?php esc_html_e('Remove', 'email-redirect'); ?></button>
            </td>
        </tr>
        <?php
    }

    public function register_widget() {
        require_once plugin_dir_path(__FILE__) . 'widget.php';
        register_widget('Email_Redirect_Widget');
    }

    public function render_shortcode($atts) {
        $atts = shortcode_atts(array(
            'title' => '',
        ), $atts, 'email_redirect_form');

        $unique_id = uniqid('er-form-');

        ob_start();
        ?>
        <div class="er-widget-container">
            <?php if (!empty($atts['title'])) : ?>
                <h3 class="er-form-title"><?php echo esc_html($atts['title']); ?></h3>
            <?php endif; ?>
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
        return ob_get_clean();
    }

    public function enqueue_scripts() {
        wp_enqueue_script('jquery');
        wp_enqueue_script(
            'er-script',
            plugin_dir_url(__FILE__) . 'script.js',
            array('jquery'),
            '1.0.0',
            true
        );

        wp_localize_script('er-script', 'erAjax', array(
            'ajaxurl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('er_nonce')
        ));

        wp_enqueue_style(
            'er-style',
            plugin_dir_url(__FILE__) . 'style.css',
            array(),
            '1.0.0'
        );
    }

    public function process_email() {
        check_ajax_referer('er_nonce', 'nonce');

        $email = isset($_POST['email']) ? sanitize_email(wp_unslash($_POST['email'])) : '';

        if (!is_email($email)) {
            wp_send_json_error(array('message' => __('Please enter a valid email address.', 'email-redirect')));
        }

        $domain = $this->extract_domain($email);
        $redirect_url = $this->find_redirect_url($domain);

        if ($redirect_url) {
            wp_send_json_success(array('url' => $redirect_url));
        } else {
            wp_send_json_error(array('message' => __('No redirect URL found for your email domain.', 'email-redirect')));
        }
    }

    private function extract_domain($email) {
        $parts = explode('@', $email);
        return isset($parts[1]) ? strtolower($parts[1]) : '';
    }

    private function find_redirect_url($domain) {
        $mappings = get_option($this->option_name, array());

        // First, try exact match (including subdomain)
        foreach ($mappings as $mapping) {
            if (strtolower($mapping['domain']) === $domain) {
                return $mapping['url'];
            }
        }

        // Then, try main domain match
        $main_domain = $this->extract_main_domain($domain);
        foreach ($mappings as $mapping) {
            if (strtolower($mapping['domain']) === $main_domain) {
                return $mapping['url'];
            }
        }

        return false;
    }

    private function extract_main_domain($domain) {
        $parts = explode('.', $domain);
        $count = count($parts);

        // Handle cases like co.uk, com.au, etc.
        if ($count >= 3 && strlen($parts[$count - 2]) <= 3 && strlen($parts[$count - 1]) <= 2) {
            return implode('.', array_slice($parts, -3));
        }

        // Standard domain
        if ($count >= 2) {
            return implode('.', array_slice($parts, -2));
        }

        return $domain;
    }
}

// Initialize plugin
new Email_Redirect();
