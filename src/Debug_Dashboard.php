<?php
namespace JWTCookieBridge;

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
           'jwt-cookie-bridge-debug',
           [$this, 'render_debug_page'],
           'dashicons-visibility'
       );
   }

   public function handle_reset_token() {
       check_admin_referer('reset_sso_token');
       delete_transient('jwt_bridge_token_status');
       $cookie_manager = new Cookie_Manager();
       $result = $cookie_manager->clear_token_cookie();
       wp_redirect(admin_url('admin.php?page=jwt-cookie-bridge-debug&reset=' . ($result ? '1' : '0')));
       exit;
   }

   public function render_debug_page() {
       if (!current_user_can('manage_options')) {
           return;
       }

       $token_status = Token_Handler::get_token_status();
       ?>
       <div class="wrap">
           <h1>SSO Debug Dashboard</h1>
           
           <form action="<?php echo esc_url(admin_url('admin-post.php')); ?>" method="post" style="margin: 20px 0;">
               <input type="hidden" name="action" value="reset_sso_token">
               <?php wp_nonce_field('reset_sso_token'); ?>
               <button type="submit" class="button button-secondary">Reset SSO Token</button>
           </form>

           <h2>SSO Token Process Status</h2>
           <table class="widefat" style="max-width: 600px; margin-top: 20px;">
               <tr>
                   <th>Last Token Process</th>
                   <td>
                       <?php 
                       if (!empty($token_status['timestamp'])) {
                           $time_diff = human_time_diff($token_status['timestamp'], current_time('timestamp'));
                           echo esc_html($time_diff) . ' ago';
                           echo ' (' . date('Y-m-d H:i:s', $token_status['timestamp']) . ')';
                       } else {
                           echo 'No token process recorded';
                       }
                       ?>
                   </td>
               </tr>
               <tr>
                   <th>Hook Triggered</th>
                   <td><?php echo $this->status_indicator($token_status['hook_triggered'] ?? false); ?></td>
               </tr>
               <tr>
                   <th>Valid User</th>
                   <td><?php echo $this->status_indicator($token_status['user_valid'] ?? false); ?></td>
               </tr>
               <tr>
                   <th>Token Received</th>
                   <td><?php echo $this->status_indicator($token_status['token_received'] ?? false); ?></td>
               </tr>
               <tr>
                   <th>Token Valid</th>
                   <td><?php echo $this->status_indicator($token_status['token_valid'] ?? false); ?></td>
               </tr>
               <tr>
                   <th>Cookie Set Attempt</th>
                   <td><?php echo $this->status_indicator($token_status['cookie_set'] ?? false); ?></td>
               </tr>
           </table>

           <h2>System Status</h2>
           <table class="widefat" style="max-width: 600px; margin-top: 20px;">
               <tr>
                   <th>WordPress User Logged In</th>
                   <td><?php echo $this->status_indicator(is_user_logged_in()); ?></td>
               </tr>
               <tr>
                   <th>Headers Already Sent</th>
                   <td><?php 
                       $headers_sent = headers_sent($file, $line);
                       echo $this->status_indicator(!$headers_sent, $headers_sent ? "at $file:$line" : '');
                   ?></td>
               </tr>
               <tr>
                   <th>Cookie Settings</th>
                   <td>
                       Name: <?php echo esc_html(Settings_Manager::get_cookie_name()); ?><br>
                       SameSite: <?php echo esc_html(Settings_Manager::get_samesite_policy()); ?><br>
                       HttpOnly: <?php echo Settings_Manager::is_http_only() ? 'Yes' : 'No'; ?><br>
                       Duration: <?php echo esc_html(Settings_Manager::get_cookie_duration()); ?> seconds
                   </td>
               </tr>
           </table>

           <h2>Recent Error Logs</h2>
           <div class="log-container" style="max-height: 300px; overflow-y: auto; margin-top: 20px;">
               <pre><?php echo esc_html($this->get_recent_logs()); ?></pre>
           </div>
       </div>
       <?php
   }

   private function status_indicator($status, $details = '') {
       $icon = $status ? '✅' : '❌';
       $text = $status ? 'Yes' : 'No';
       return $icon . ' ' . $text . ($details ? ' ' . $details : '');
   }

   private function get_recent_logs() {
       $log_file = WP_CONTENT_DIR . '/debug.log';
       if (!file_exists($log_file)) {
           return 'No debug log file found';
       }

       $logs = shell_exec('tail -n 50 ' . escapeshellarg($log_file));
       return $logs ?: 'Unable to read log file';
   }
}