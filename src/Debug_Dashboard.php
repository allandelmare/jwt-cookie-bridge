<?php
namespace ChristendomSSO;

class Debug_Dashboard {
    public function __construct() {
        if (!Settings_Manager::is_debug_enabled()) {
            return;
        }
        
        add_action('admin_menu', [$this, 'add_debug_menu']);
        add_action('admin_post_reset_sso_token', [$this, 'handle_reset_token']);
    }

    public function add_debug_menu() {
        add_menu_page(
            'SSO Debug',
            'SSO Debug',
            'manage_options',
            'christendom-sso-debug',
            [$this, 'render_debug_page'],
            'dashicons-visibility'
        );
    }

    public function handle_reset_token() {
        check_admin_referer('reset_sso_token');
        
        // Force cookie deletion immediately
        setcookie(Cookie_Manager::COOKIE_NAME, '', time() - 3600, '/');
        setcookie(Cookie_Manager::COOKIE_NAME, '', time() - 3600, '/wp-admin');
        
        // Then try the cookie manager's method
        $cookie_manager = new Cookie_Manager();
        $result = $cookie_manager->clear_token_cookie();
        
        error_log('SSO token reset attempted. Result: ' . ($result ? 'success' : 'failed'));
        
        wp_redirect(admin_url('admin.php?page=christendom-sso-debug&reset=' . ($result ? '1' : '0')));
        exit;
    }

    public function render_debug_page() {
        if (!current_user_can('manage_options')) {
            return;
        }

        // Clear cookie display value if reset was requested
        if (isset($_GET['reset']) && $_GET['reset'] === '1') {
            unset($_COOKIE[Cookie_Manager::COOKIE_NAME]);
        }

        // Show appropriate message based on reset result
        if (isset($_GET['reset'])) {
            $success = $_GET['reset'] === '1';
            $message_class = $success ? 'success' : 'error';
            $message = $success ? 'Token reset successfully.' : 'Token reset failed. Check error logs for details.';
            echo '<div class="notice notice-' . esc_attr($message_class) . '"><p>' . esc_html($message) . '</p></div>';
        }

        $cookie_name = Cookie_Manager::COOKIE_NAME;
        $current_token = $_COOKIE[$cookie_name] ?? null;
        ?>
        <div class="wrap">
            <h1>SSO Debug Dashboard</h1>
            
            <form action="<?php echo esc_url(admin_url('admin-post.php')); ?>" method="post" style="margin: 20px 0;">
                <input type="hidden" name="action" value="reset_sso_token">
                <?php wp_nonce_field('reset_sso_token'); ?>
                <button type="submit" class="button button-secondary">Reset SSO Token</button>
            </form>

            <h2>System Status</h2>
            <table class="widefat" style="max-width: 600px; margin-top: 20px;">
                <tr>
                    <th>Cookie Name</th>
                    <td><?php echo esc_html($cookie_name); ?></td>
                </tr>
                <tr>
                    <th>Cookie Present</th>
                    <td><?php echo $current_token ? '✅ Yes' : '❌ No'; ?></td>
                </tr>
                <tr>
                    <th>Headers Already Sent</th>
                    <td><?php echo headers_sent($file, $line) ? "✅ Yes ($file:$line)" : '❌ No'; ?></td>
                </tr>
                <tr>
                    <th>Current Domain</th>
                    <td><?php echo esc_html($_SERVER['HTTP_HOST']); ?></td>
                </tr>
                <tr>
                    <th>Browser</th>
                    <td><?php echo esc_html($_SERVER['HTTP_USER_AGENT']); ?></td>
                </tr>
                <tr>
                    <th>Available Cookies</th>
                    <td><pre><?php echo esc_html(print_r($_COOKIE, true)); ?></pre></td>
                </tr>
                <?php if ($current_token): ?>
                <tr>
                    <th>Token Length</th>
                    <td><?php echo strlen($current_token); ?> characters</td>
                </tr>
                <tr>
                    <th>Token Preview</th>
                    <td><?php echo esc_html(substr($current_token, 0, 50) . '...'); ?></td>
                </tr>
                <?php endif; ?>
            </table>
        </div>
        <?php
    }
}