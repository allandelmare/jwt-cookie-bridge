<?php
namespace JWTCookieBridge;

/**
 * Handles JWT token processing and validation
 */
class Token_Handler {
    private const TRANSIENT_KEY = 'jwt_bridge_token_status';
    private const MIN_TOKEN_LENGTH = 32;

    public function __construct() {
        add_action('mo_oauth_logged_in_user_token', [$this, 'handle_sso_token'], 10, 2);
    }

    /**
     * Process SSO token from MiniOrange
     *
     * @param \WP_User|bool $user WordPress user object
     * @param array|bool $token Token data from OAuth provider
     */
    public function handle_sso_token($user, $token): void {
        $status = $this->initialize_status();

        if (!$user instanceof \WP_User || !is_array($token)) {
            $this->log_error('Invalid user or token data received');
            $this->save_status($status);
            return;
        }

        if (!$this->validate_token($token)) {
            $this->log_error('Token validation failed');
            $this->save_status($status);
            return;
        }

        $status['token_valid'] = true;
        $access_token = $token['access_token'] ?? null;

        if (!$access_token) {
            $this->log_error('Access token missing from token data');
            $this->save_status($status);
            return;
        }

        try {
            $cookie_manager = new Cookie_Manager();
            $result = $cookie_manager->set_token_cookie($access_token);
            $status['cookie_set'] = $result;
            
            $this->log_error('Cookie set result: ' . ($result ? 'success' : 'failed'));
        } catch (\Exception $e) {
            $this->log_error('Error handling SSO token: ' . $e->getMessage());
            $status['cookie_set'] = false;
        }

        $this->save_status($status);
    }

    /**
     * Initialize token status array
     *
     * @return array Status array
     */
    private function initialize_status(): array {
        if (!Settings_Manager::is_debug_enabled()) {
            return [];
        }

        return [
            'timestamp' => time(),
            'hook_triggered' => true,
            'user_valid' => false,
            'token_received' => false,
            'token_valid' => false,
            'cookie_set' => false
        ];
    }

    /**
     * Save token status to transient
     *
     * @param array $status Status data
     */
    private function save_status(array $status): void {
        if (Settings_Manager::is_debug_enabled()) {
            set_transient(self::TRANSIENT_KEY, $status, DAY_IN_SECONDS);
        }
    }

    /**
     * Log error message with plugin prefix
     *
     * @param string $message Error message
     */
    private function log_error(string $message): void {
        error_log('JWT Cookie Bridge: ' . $message);
    }

    /**
     * Validate token structure and contents
     *
     * @param array $token Token data to validate
     * @return bool Validation result
     */
    private function validate_token(array $token): bool {
        if (!isset($token['access_token']) || !is_string($token['access_token'])) {
            $this->log_error('Invalid access_token format');
            return false;
        }

        $access_token = $token['access_token'];

        if (strlen($access_token) < self::MIN_TOKEN_LENGTH) {
            $this->log_error('Token length below minimum requirement');
            return false;
        }

        if (!preg_match('/^[a-zA-Z0-9._-]+$/', $access_token)) {
            $this->log_error('Token contains invalid characters');
            return false;
        }

        return true;
    }

    /**
     * Get current token process status
     *
     * @return array Status data
     */
    public static function get_token_status(): array {
        return get_transient(self::TRANSIENT_KEY) ?: [];
    }

    /**
     * Get current user's token
     *
     * @return string|null Token or null if not found
     */
    public function get_current_token_data(): ?string {
        if (!is_user_logged_in()) {
            return null;
        }

        $cookie_manager = new Cookie_Manager();
        return $cookie_manager->get_token_cookie();
    }
}