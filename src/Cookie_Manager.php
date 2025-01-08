<?php
namespace JWTCookieBridge;

/**
 * Handles cookie operations for JWT tokens
 */
class Cookie_Manager {
    
    /**
     * Set JWT token as secure cookie
     *
     * @param string $token The JWT token to store
     * @return bool Success status
     */
    public function set_token_cookie(string $token): bool {
        if (!$this->validate_token($token)) {
            error_log('JWT Cookie Bridge: Invalid token format');
            return false;
        }

        if (headers_sent($file, $line)) {
            error_log(sprintf('JWT Cookie Bridge: Headers already sent in %s:%s', $file, $line));
            return false;
        }

        $cookie_name = Settings_Manager::get_cookie_name();
        $duration = Settings_Manager::get_cookie_duration();
        
        $cookie_options = [
            'expires' => time() + $duration,
            'path' => '/',
            'domain' => $this->get_cookie_domain(),
            'secure' => true,
            'httponly' => Settings_Manager::is_http_only(),
            'samesite' => Settings_Manager::get_samesite_policy()
        ];

        try {
            $result = setcookie(
                $cookie_name,
                $token,
                $cookie_options
            );

            if ($result) {
                $_COOKIE[$cookie_name] = $token;
            }

            return $result;
        } catch (\Exception $e) {
            error_log('JWT Cookie Bridge: Error setting cookie: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Get stored JWT token from cookie
     *
     * @return string|null Token or null if not found
     */
    public function get_token_cookie(): ?string {
        $cookie_name = Settings_Manager::get_cookie_name();
        
        if (!isset($_COOKIE[$cookie_name])) {
            return null;
        }

        $token = $_COOKIE[$cookie_name];
        
        if (!$this->validate_token($token)) {
            $this->clear_token_cookie();
            return null;
        }

        return $token;
    }

    /**
     * Clear JWT token cookie
     *
     * @return bool Success status
     */
    public function clear_token_cookie(): bool {
        $cookie_name = Settings_Manager::get_cookie_name();
        $paths = ['/', '/wp-admin', '/wp-content', ''];
        $domain = $this->get_cookie_domain();
        
        // Unset PHP cookie
        unset($_COOKIE[$cookie_name]);
        
        if (headers_sent()) {
            error_log('JWT Cookie Bridge: Headers already sent, cookie removal may be incomplete');
            return false;
        }

        // Clear cookie for all common paths
        foreach ($paths as $path) {
            setcookie($cookie_name, '', [
                'expires' => time() - 3600,
                'path' => $path,
                'domain' => $domain,
                'secure' => true,
                'httponly' => Settings_Manager::is_http_only(),
                'samesite' => Settings_Manager::get_samesite_policy()
            ]);
        }

        return true;
    }

    /**
     * Validate JWT token format
     *
     * @param string $token Token to validate
     * @return bool Validation result
     */
    private function validate_token(string $token): bool {
        if (empty($token)) {
            return false;
        }

        // Basic JWT format validation (3 dot-separated base64url-encoded sections)
        $parts = explode('.', $token);
        if (count($parts) !== 3) {
            return false;
        }

        foreach ($parts as $part) {
            if (!preg_match('/^[a-zA-Z0-9_-]+$/', $part)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Get cookie domain based on site URL
     *
     * @return string Cookie domain
     */
    private function get_cookie_domain(): string {
        $site_url = get_site_url();
        $parsed_url = wp_parse_url($site_url);
        return $parsed_url['host'] ?? '';
    }
}