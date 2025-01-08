<?php
namespace JWTCookieBridge;

class Token_Handler {
    private const TRANSIENT_KEY = 'jwt_bridge_token_status';

    public function __construct() {
        add_action('mo_oauth_logged_in_user_token', array($this, 'handle_sso_token'), 10, 2);
    }

    public function handle_sso_token($user, $token) {
        if (Settings_Manager::is_debug_enabled()) {
            $status = [
                'timestamp' => current_time('timestamp'),
                'hook_triggered' => true,
                'user_valid' => (bool)$user,
                'token_received' => (bool)$token,
                'token_valid' => false,
                'cookie_set' => false
            ];
        }

        if (!$user || !$token || !is_array($token)) {
            error_log('JWT Cookie Bridge: Invalid user or token data received');
            if (Settings_Manager::is_debug_enabled()) {
                set_transient(self::TRANSIENT_KEY, $status);
            }
            return;
        }

        if (!$this->validate_token($token)) {
            error_log('JWT Cookie Bridge: Token validation failed');
            if (Settings_Manager::is_debug_enabled()) {
                set_transient(self::TRANSIENT_KEY, $status);
            }
            return;
        }

        if (Settings_Manager::is_debug_enabled()) {
            $status['token_valid'] = true;
        }

        try {
            $cookie_manager = new Cookie_Manager();
            $access_token = $token['access_token'] ?? null;
            
            if ($access_token) {
                $result = $cookie_manager->set_token_cookie($access_token);
                if (Settings_Manager::is_debug_enabled()) {
                    $status['cookie_set'] = $result;
                }
                error_log('JWT Cookie Bridge: Cookie set result: ' . ($result ? 'success' : 'failed'));
            }

        } catch (\Exception $e) {
            error_log('JWT Cookie Bridge: Error handling SSO token: ' . $e->getMessage());
        }

        if (Settings_Manager::is_debug_enabled()) {
            set_transient(self::TRANSIENT_KEY, $status);
        }
    }

    private function validate_token($token) {
        if (!isset($token['access_token']) || empty($token['access_token'])) {
            error_log('JWT Cookie Bridge: Token validation failed: missing access_token');
            return false;
        }

        if (!is_string($token['access_token']) || strlen($token['access_token']) < 32) {
            error_log('JWT Cookie Bridge: Token validation failed: invalid access_token format');
            return false;
        }

        return true;
    }

    public static function get_token_status() {
        return get_transient(self::TRANSIENT_KEY) ?: [];
    }

    public function get_current_token_data() {
        if (!is_user_logged_in()) {
            return null;
        }

        $cookie_manager = new Cookie_Manager();
        return $cookie_manager->get_token_cookie();
    }
}