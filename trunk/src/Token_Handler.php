<?php
namespace JWTCookieBridge;

/**
 * Handles JWT token processing, validation, and refresh
 */
class Token_Handler {
    private const TRANSIENT_KEY = 'jwt_bridge_token_status';
    private const MIN_TOKEN_LENGTH = 32;
    private const TOKEN_LEEWAY = 60; // Seconds of leeway for token validation

    private $cookie_manager;

    public function __construct() {
        $this->cookie_manager = new Cookie_Manager();
        add_action('mo_oauth_logged_in_user_token', [$this, 'handle_sso_token'], 10, 2);
        add_action('wp_logout', [$this, 'handle_logout']);
    }

    /**
     * Process SSO token from MiniOrange
     *
     * @param \WP_User|bool $user WordPress user object
     * @param array|bool $token Token data from OAuth provider
     */
    public function handle_sso_token($user, $token): void {
        $status = $this->initialize_status();

        try {
            if (!$this->validate_input($user, $token, $status)) {
                $this->save_status($status);
                return;
            }

            $access_token = $token['access_token'];
            $refresh_token = $token['refresh_token'] ?? null;

            if (!$this->validate_token_structure($access_token)) {
                throw new \Exception('Invalid token structure');
            }

            $decoded_token = $this->decode_token($access_token);
            if (!$this->validate_token_claims($decoded_token)) {
                throw new \Exception('Invalid token claims');
            }

            $status['token_valid'] = true;
            $result = $this->cookie_manager->set_token_cookie($access_token);
            
            if ($refresh_token) {
                $this->store_refresh_token($user->ID, $refresh_token);
            }

            $status['cookie_set'] = $result;
            $this->log_message('Token successfully processed and stored');

        } catch (\Exception $e) {
            $this->log_error('Token processing error: ' . $e->getMessage());
            $status['token_valid'] = false;
            $status['cookie_set'] = false;
        }

        $this->save_status($status);
    }

    /**
     * Handle user logout
     */
    public function handle_logout(): void {
        try {
            $this->cookie_manager->clear_token_cookie();
            $this->clear_refresh_token(get_current_user_id());
            $this->log_message('Token cleared on logout');
        } catch (\Exception $e) {
            $this->log_error('Logout error: ' . $e->getMessage());
        }
    }

    /**
     * Validate input parameters
     *
     * @param mixed $user User object
     * @param mixed $token Token data
     * @param array $status Status array
     * @return bool Validation result
     */
    private function validate_input($user, $token, array &$status): bool {
        if (!$user instanceof \WP_User) {
            $this->log_error('Invalid user object received');
            return false;
        }

        if (!is_array($token)) {
            $this->log_error('Invalid token data received');
            return false;
        }

        if (!isset($token['access_token'])) {
            $this->log_error('Access token missing from token data');
            return false;
        }

        $status['user_valid'] = true;
        $status['token_received'] = true;
        return true;
    }

    /**
     * Validate token structure
     *
     * @param string $token JWT token
     * @return bool Validation result
     */
    private function validate_token_structure(string $token): bool {
        if (strlen($token) < self::MIN_TOKEN_LENGTH) {
            throw new \Exception('Token length below minimum requirement');
        }

        $parts = explode('.', $token);
        if (count($parts) !== 3) {
            throw new \Exception('Invalid JWT format');
        }

        foreach ($parts as $part) {
            if (!preg_match('/^[a-zA-Z0-9_-]+$/', $part)) {
                throw new \Exception('Invalid token character set');
            }
        }

        return true;
    }

    /**
     * Decode JWT token
     *
     * @param string $token JWT token
     * @return array Decoded token payload
     * @throws \Exception If token cannot be decoded
     */
    private function decode_token(string $token): array {
        $parts = explode('.', $token);
        $payload = base64_decode(strtr($parts[1], '-_', '+/'));
        
        if ($payload === false) {
            throw new \Exception('Failed to decode token payload');
        }

        $decoded = json_decode($payload, true);
        if (!is_array($decoded)) {
            throw new \Exception('Invalid token payload structure');
        }

        return $decoded;
    }

    /**
     * Validate token claims
     *
     * @param array $payload Decoded token payload
     * @return bool Validation result
     */
    private function validate_token_claims(array $payload): bool {
        $time = time();

        // Check required claims
        if (!isset($payload['exp'])) {
            throw new \Exception('Token missing expiration claim');
        }

        // Validate expiration with leeway
        if ($payload['exp'] <= ($time - self::TOKEN_LEEWAY)) {
            throw new \Exception('Token has expired');
        }

        // Validate not before claim if present
        if (isset($payload['nbf']) && $payload['nbf'] > ($time + self::TOKEN_LEEWAY)) {
            throw new \Exception('Token not yet valid');
        }

        return true;
    }

    /**
     * Store refresh token for user
     *
     * @param int $user_id User ID
     * @param string $refresh_token Refresh token
     */
    private function store_refresh_token(int $user_id, string $refresh_token): void {
        update_user_meta($user_id, 'jwt_refresh_token', wp_hash($refresh_token));
    }

    /**
     * Clear refresh token for user
     *
     * @param int $user_id User ID
     */
    private function clear_refresh_token(int $user_id): void {
        delete_user_meta($user_id, 'jwt_refresh_token');
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
        if (Settings_Manager::is_debug_enabled()) {
            error_log('JWT Cookie Bridge Error: ' . $message);
        }
    }

    /**
     * Log informational message
     *
     * @param string $message Info message
     */
    private function log_message(string $message): void {
        if (Settings_Manager::is_debug_enabled()) {
            error_log('JWT Cookie Bridge: ' . $message);
        }
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

        return $this->cookie_manager->get_token_cookie();
    }
}