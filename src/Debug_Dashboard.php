<?php
namespace JWTCookieBridge;

/**
 * Debug dashboard for monitoring token processes
 */
class Debug_Dashboard {
    public function __construct() {
        if (!Settings_Manager::is_debug_enabled()) {
            return;
        }
        add_action('admin_menu', [$this, 'add_debug_menu']);
        add_action('admin_post_reset_sso_token', [$this, 'handle_reset_token']);
    }

    /**
     * Add debug menu to admin panel
     */
    public function add_debug_menu(): void {
        add_menu_page(
            __('SSO Debug', 'jwt-cookie-bridge'),
            __('SSO Debug', 'jwt-cookie-bridge'),
            'manage_options',
            'jwt-cookie-bridge-debug',
            [$this, 'render_debug_page'],
            'dashicons-visibility'
        );
    }

    /**
     * Handle token reset action
     */
    public function handle_reset_token(): void {
        if (!current_user_can('manage_options')) {
            wp_die(__('Unauthorized access', 'jwt-cookie-bridge'));
        }

        check_admin_referer('reset_sso_token');
        delete_transient('jwt_bridge_token_status');
        
        $cookie_manager = new Cookie_Manager();
        $result = $cookie_manager->clear_token_cookie();
        
        wp_safe_redirect(add_query_arg(
            ['page' => 'jwt-cookie-bridge-debug', 'reset' => $result ? '1' : '0'],
            admin_url('admin.php')
        ));
        exit;
    }

    /**
     * Render debug dashboard page
     */
    public function render_debug_page(): void {
        if (!current_user_can('manage_options')) {
            wp_die(__('Unauthorized access', 'jwt-cookie-bridge'));
        }

        $token_status = Token_Handler::get_token_status();
        ?>
        <div class="wrap">
            <h1><?php esc_html_e('SSO Debug Dashboard', 'jwt-cookie-bridge'); ?></h1>
            
            <?php $this->render_reset_form(); ?>
            <?php $this->render_token_status($token_status); ?>
            <?php $this->render_system_status(); ?>
            <?php $this->render_error_logs(); ?>
        </div>
        <?php
    }

    /**
     * Render token reset form
     */
    private function render_reset_form(): void {
        ?>
        <form action="<?php echo esc_url(admin_url('admin-post.php')); ?>" method="post" class="jwt-reset-form">
            <input type="hidden" name="action" value="reset_sso_token">
            <?php wp_nonce_field('reset_sso_token'); ?>
            <button type="submit" class="button button-secondary">
                <?php esc_html_e('Reset SSO Token', 'jwt-cookie-bridge'); ?>
            </button>
        </form>
        <?php
    }

    /**
     * Render token status section
     * 
     * @param array $token_status Token status data
     */
    private function render_token_status(array $token_status): void {
        ?>
        <h2><?php esc_html_e('SSO Token Process Status', 'jwt-cookie-bridge'); ?></h2>
        <table class="widefat jwt-status-table">
            <tr>
                <th><?php esc_html_e('Last Token Process', 'jwt-cookie-bridge'); ?></th>
                <td>
                    <?php
                    if (!empty($token_status['timestamp'])) {
                        $time_diff = human_time_diff($token_status['timestamp'], current_time('timestamp'));
                        echo esc_html(sprintf(
                            /* translators: %1$s: time difference, %2$s: formatted date */
                            __('%1$s ago (%2$s)', 'jwt-cookie-bridge'),
                            $time_diff,
                            wp_date(get_option('date_format') . ' ' . get_option('time_format'), $token_status['timestamp'])
                        ));
                    } else {
                        esc_html_e('No token process recorded', 'jwt-cookie-bridge');
                    }
                    ?>
                </td>
            </tr>
            <?php
            $status_fields = [
                'hook_triggered' => __('Hook Triggered', 'jwt-cookie-bridge'),
                'user_valid' => __('Valid User', 'jwt-cookie-bridge'),
                'token_received' => __('Token Received', 'jwt-cookie-bridge'),
                'token_valid' => __('Token Valid', 'jwt-cookie-bridge'),
                'cookie_set' => __('Cookie Set Attempt', 'jwt-cookie-bridge')
            ];

            foreach ($status_fields as $field => $label) {
                ?>
                <tr>
                    <th><?php echo esc_html($label); ?></th>
                    <td><?php echo $this->status_indicator($token_status[$field] ?? false); ?></td>
                </tr>
                <?php
            }
            ?>
        </table>
        <?php
    }

    /**
     * Render system status section
     */
    private function render_system_status(): void {
        ?>
        <h2><?php esc_html_e('System Status', 'jwt-cookie-bridge'); ?></h2>
        <table class="widefat jwt-system-table">
            <tr>
                <th><?php esc_html_e('WordPress User Logged In', 'jwt-cookie-bridge'); ?></th>
                <td><?php echo $this->status_indicator(is_user_logged_in()); ?></td>
            </tr>
            <tr>
                <th><?php esc_html_e('Headers Already Sent', 'jwt-cookie-bridge'); ?></th>
                <td><?php
                    $headers_sent = headers_sent($file, $line);
                    echo $this->status_indicator(
                        !$headers_sent,
                        $headers_sent ? sprintf('at %s:%s', esc_html($file), esc_html($line)) : ''
                    );
                ?></td>
            </tr>
            <tr>
                <th><?php esc_html_e('Cookie Settings', 'jwt-cookie-bridge'); ?></th>
                <td>
                    <?php
                    $settings = [
                        __('Name', 'jwt-cookie-bridge') => Settings_Manager::get_cookie_name(),
                        __('SameSite', 'jwt-cookie-bridge') => Settings_Manager::get_samesite_policy(),
                        __('HttpOnly', 'jwt-cookie-bridge') => Settings_Manager::is_http_only() ? __('Yes', 'jwt-cookie-bridge') : __('No', 'jwt-cookie-bridge'),
                        __('Duration', 'jwt-cookie-bridge') => sprintf(
                            /* translators: %d: duration in seconds */
                            __('%d seconds', 'jwt-cookie-bridge'),
                            Settings_Manager::get_cookie_duration()
                        )
                    ];

                    foreach ($settings as $label => $value) {
                        echo esc_html($label) . ': ' . esc_html($value) . '<br>';
                    }
                    ?>
                </td>
            </tr>
        </table>
        <?php
    }

    /**
     * Render error logs section
     */
    private function render_error_logs(): void {
        ?>
        <h2><?php esc_html_e('Recent Error Logs', 'jwt-cookie-bridge'); ?></h2>
        <div class="jwt-log-container">
            <pre><?php echo esc_html($this->get_recent_logs()); ?></pre>
        </div>
        <?php
    }

    /**
     * Generate status indicator HTML
     *
     * @param bool $status Status value
     * @param string $details Optional details
     * @return string Formatted status indicator
     */
    private function status_indicator(bool $status, string $details = ''): string {
        $icon = $status ? '✅' : '❌';
        $text = $status ? __('Yes', 'jwt-cookie-bridge') : __('No', 'jwt-cookie-bridge');
        return esc_html($icon . ' ' . $text . ($details ? ' ' . $details : ''));
    }

    /**
     * Get recent log entries
     *
     * @return string Log entries
     */
    private function get_recent_logs(): string {
        $log_file = WP_CONTENT_DIR . '/debug.log';
        
        if (!file_exists($log_file)) {
            return __('No debug log file found', 'jwt-cookie-bridge');
        }

        if (!is_readable($log_file)) {
            return __('Debug log file is not readable', 'jwt-cookie-bridge');
        }

        $logs = shell_exec('tail -n 50 ' . escapeshellarg($log_file));
        return $logs ?: __('Unable to read log file', 'jwt-cookie-bridge');
    }
}