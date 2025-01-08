<?php
namespace JWTCookieBridge;

/**
 * Enhanced debug dashboard for monitoring token processes
 */
class Debug_Dashboard {
    private const MAX_LOG_LINES = 50;
    private const LOG_REFRESH_INTERVAL = 300; // 5 minutes
    private $last_refresh;

    public function __construct() {
        if (!Settings_Manager::is_debug_enabled()) {
            return;
        }
        
        add_action('admin_menu', [$this, 'add_debug_menu']);
        add_action('admin_post_reset_sso_token', [$this, 'handle_reset_token']);
        add_action('admin_post_clear_debug_logs', [$this, 'handle_clear_logs']);
        add_action('admin_enqueue_scripts', [$this, 'enqueue_scripts']);
        
        $this->last_refresh = get_transient('jwt_debug_last_refresh') ?: 0;
    }

    /**
     * Add debug menu to admin panel with capability check
     */
    public function add_debug_menu(): void {
        if (!current_user_can('manage_options')) {
            return;
        }

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
     * Enqueue admin scripts and styles
     */
    public function enqueue_scripts($hook): void {
        if ($hook !== 'toplevel_page_jwt-cookie-bridge-debug') {
            return;
        }

        wp_enqueue_style(
            'jwt-debug-styles',
            JWT_COOKIE_BRIDGE_PLUGIN_URL . 'assets/css/debug.css',
            [],
            JWT_COOKIE_BRIDGE_VERSION
        );

        wp_enqueue_script(
            'jwt-debug-scripts',
            JWT_COOKIE_BRIDGE_PLUGIN_URL . 'assets/js/debug.js',
            ['jquery'],
            JWT_COOKIE_BRIDGE_VERSION,
            true
        );
    }

    /**
     * Handle token reset action with security checks
     */
    public function handle_reset_token(): void {
        try {
            $this->verify_admin_request('reset_sso_token');

            $cookie_manager = new Cookie_Manager();
            $result = $cookie_manager->clear_token_cookie();
            delete_transient('jwt_bridge_token_status');
            
            $this->redirect_with_result('reset', $result);
        } catch (\Exception $e) {
            $this->redirect_with_result('reset', false, $e->getMessage());
        }
    }

    /**
     * Handle clear logs action with security checks
     */
    public function handle_clear_logs(): void {
        try {
            $this->verify_admin_request('clear_debug_logs');
            
            $log_file = WP_CONTENT_DIR . '/debug.log';
            if (file_exists($log_file) && is_writable($log_file)) {
                file_put_contents($log_file, '');
                $this->redirect_with_result('clear_logs', true);
            } else {
                throw new \Exception(__('Log file not accessible', 'jwt-cookie-bridge'));
            }
        } catch (\Exception $e) {
            $this->redirect_with_result('clear_logs', false, $e->getMessage());
        }
    }

    /**
     * Verify admin request with security checks
     */
    private function verify_admin_request(string $action): void {
        if (!current_user_can('manage_options')) {
            wp_die(__('Unauthorized access', 'jwt-cookie-bridge'));
        }

        check_admin_referer($action);
    }

    /**
     * Redirect with status and message
     */
    private function redirect_with_result(string $action, bool $success, string $error = ''): void {
        $params = [
            'page' => 'jwt-cookie-bridge-debug',
            $action => $success ? '1' : '0'
        ];

        if (!empty($error)) {
            $params['error'] = urlencode($error);
        }

        wp_safe_redirect(add_query_arg($params, admin_url('admin.php')));
        exit;
    }

    /**
     * Render debug dashboard page with security checks
     */
    public function render_debug_page(): void {
        if (!current_user_can('manage_options')) {
            wp_die(__('Unauthorized access', 'jwt-cookie-bridge'));
        }

        $this->handle_admin_notices();
        ?>
        <div class="wrap">
            <h1><?php esc_html_e('SSO Debug Dashboard', 'jwt-cookie-bridge'); ?></h1>
            
            <div class="jwt-debug-container">
                <?php
                $this->render_action_buttons();
                $this->render_token_status();
                $this->render_system_status();
                $this->render_error_logs();
                ?>
            </div>
        </div>
        <?php
    }

    /**
     * Handle admin notices for actions
     */
    private function handle_admin_notices(): void {
        if (isset($_GET['reset'])) {
            $type = $_GET['reset'] === '1' ? 'success' : 'error';
            $message = $_GET['reset'] === '1' 
                ? __('Token reset successful', 'jwt-cookie-bridge')
                : __('Token reset failed', 'jwt-cookie-bridge');
            $this->display_admin_notice($message, $type);
        }

        if (isset($_GET['clear_logs'])) {
            $type = $_GET['clear_logs'] === '1' ? 'success' : 'error';
            $message = $_GET['clear_logs'] === '1'
                ? __('Logs cleared successfully', 'jwt-cookie-bridge')
                : __('Failed to clear logs', 'jwt-cookie-bridge');
            $this->display_admin_notice($message, $type);
        }

        if (isset($_GET['error'])) {
            $this->display_admin_notice(urldecode($_GET['error']), 'error');
        }
    }

    /**
     * Display admin notice
     */
    private function display_admin_notice(string $message, string $type): void {
        printf(
            '<div class="notice notice-%s is-dismissible"><p>%s</p></div>',
            esc_attr($type),
            esc_html($message)
        );
    }

    /**
     * Render action buttons section
     */
    private function render_action_buttons(): void {
        ?>
        <div class="jwt-action-buttons">
            <form action="<?php echo esc_url(admin_url('admin-post.php')); ?>" method="post" class="jwt-action-form">
                <input type="hidden" name="action" value="reset_sso_token">
                <?php wp_nonce_field('reset_sso_token'); ?>
                <button type="submit" class="button button-secondary">
                    <?php esc_html_e('Reset SSO Token', 'jwt-cookie-bridge'); ?>
                </button>
            </form>

            <form action="<?php echo esc_url(admin_url('admin-post.php')); ?>" method="post" class="jwt-action-form">
                <input type="hidden" name="action" value="clear_debug_logs">
                <?php wp_nonce_field('clear_debug_logs'); ?>
                <button type="submit" class="button button-secondary">
                    <?php esc_html_e('Clear Debug Logs', 'jwt-cookie-bridge'); ?>
                </button>
            </form>
        </div>
        <?php
    }

    /**
     * Render token status section with detailed information
     */
    private function render_token_status(): void {
        $token_status = Token_Handler::get_token_status();
        ?>
        <h2><?php esc_html_e('Token Process Status', 'jwt-cookie-bridge'); ?></h2>
        <table class="widefat jwt-status-table">
            <tbody>
                <tr>
                    <th><?php esc_html_e('Last Process Time', 'jwt-cookie-bridge'); ?></th>
                    <td>
                        <?php
                        if (!empty($token_status['timestamp'])) {
                            echo esc_html(sprintf(
                                /* translators: %1$s: time ago, %2$s: formatted date */
                                __('%1$s ago (%2$s)', 'jwt-cookie-bridge'),
                                human_time_diff($token_status['timestamp'], current_time('timestamp')),
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
                    'cookie_set' => __('Cookie Set', 'jwt-cookie-bridge')
                ];

                foreach ($status_fields as $field => $label) {
                    ?>
                    <tr>
                        <th><?php echo esc_html($label); ?></th>
                        <td><?php echo $this->render_status_indicator($token_status[$field] ?? false); ?></td>
                    </tr>
                    <?php
                }
                ?>
            </tbody>
        </table>
        <?php
    }

    /**
     * Render system status section with comprehensive checks
     */
    private function render_system_status(): void {
        ?>
        <h2><?php esc_html_e('System Status', 'jwt-cookie-bridge'); ?></h2>
        <table class="widefat jwt-system-table">
            <tbody>
                <tr>
                    <th><?php esc_html_e('WordPress User Status', 'jwt-cookie-bridge'); ?></th>
                    <td><?php echo $this->render_status_indicator(is_user_logged_in()); ?></td>
                </tr>
                <tr>
                    <th><?php esc_html_e('Headers Status', 'jwt-cookie-bridge'); ?></th>
                    <td><?php
                        $headers_sent = headers_sent($file, $line);
                        echo $this->render_status_indicator(
                            !$headers_sent,
                            $headers_sent ? sprintf(__('Headers sent in %s:%s', 'jwt-cookie-bridge'), esc_html($file), esc_html($line)) : ''
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
                            printf(
                                '<strong>%s:</strong> %s<br>',
                                esc_html($label),
                                esc_html($value)
                            );
                        }
                        ?>
                    </td>
                </tr>
            </tbody>
        </table>
        <?php
    }

    /**
     * Render error logs section with pagination and filtering
     */
    private function render_error_logs(): void {
        ?>
        <h2><?php esc_html_e('Recent Error Logs', 'jwt-cookie-bridge'); ?></h2>
        <div class="jwt-log-container">
            <?php
            $logs = $this->get_filtered_logs();
            if (empty($logs)) {
                echo '<p>' . esc_html__('No logs available', 'jwt-cookie-bridge') . '</p>';
            } else {
                echo '<pre class="jwt-logs">' . esc_html($logs) . '</pre>';
            }
            ?>
        </div>
        <?php
    }

    /**
     * Get filtered and formatted logs
     */
    private function get_filtered_logs(): string {
        $log_file = WP_CONTENT_DIR . '/debug.log';
        
        if (!file_exists($log_file)) {
            return __('No debug log file found', 'jwt-cookie-bridge');
        }

        if (!is_readable($log_file)) {
            return __('Debug log file is not readable', 'jwt-cookie-bridge');
        }

        try {
            $current_time = time();
            if (($current_time - $this->last_refresh) >= self::LOG_REFRESH_INTERVAL) {
                set_transient('jwt_debug_last_refresh', $current_time, self::LOG_REFRESH_INTERVAL);
            }

            $logs = shell_exec('tail -n ' . self::MAX_LOG_LINES . ' ' . escapeshellarg($log_file));
            return $logs ?: __('No recent log entries', 'jwt-cookie-bridge');
        } catch (\Exception $e) {
            return __('Error reading log file', 'jwt-cookie-bridge') . ': ' . $e->getMessage();
        }
    }

    /**
     * Render status indicator with icon and text
     */
    private function render_status_indicator(bool $status, string $details = ''): string {
        $icon = $status ? '✅' : '❌';
        $text = $status ? __('Yes', 'jwt-cookie-bridge') : __('No', 'jwt-cookie-bridge');
        $status_text = $icon . ' ' . $text;
        
        if (!empty($details)) {
            $status_text .= ' (' . $details . ')';
        }

        return esc_html($status_text);
    }
}