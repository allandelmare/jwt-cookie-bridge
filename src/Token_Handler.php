<?php
namespace JWTCookieBridge;

class Token_Handler {
    public function __construct() {
        add_action('mo_oauth_logged_in_user_token', array($this, 'handle_sso_token'), 10, 2);
    }

    public function handle_sso_token($user, $token) {
        if (!$user || !$token || !is_array($token)) {
            error_log('JWT Cookie Bridge: Invalid user or token data received');
            return;
        }

        if (!$this->validate_token($token)) {
            error_log('JWT Cookie Bridge: Token validation failed');
            return;
        }

        try {
            $cookie_manager = new Cookie_Manager();
            $access_token = $token['access_token'] ?? null;
            
            if ($access_token) {
                $result = $cookie_manager->set_token_cookie($access_token);
                error_log('JWT Cookie Bridge: Cookie set result: ' . ($result ? 'success' : 'failed'));
            }

        } catch (\Exception $e) {
            error_log('JWT Cookie Bridge: Error handling SSO token: ' . $e->getMessage());
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

    public function get_current_token_data() {
        if (!is_user_logged_in()) {
            return null;
        }

        $cookie_manager = new Cookie_Manager();
        return $cookie_manager->get_token_cookie();
    }
}