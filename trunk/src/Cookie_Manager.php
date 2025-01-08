<?php
namespace JWTCookieBridge;

/**
 * Handles secure cookie operations for JWT tokens
 */
class Cookie_Manager {
    private const ALLOWED_DOMAINS = [
        'christendom.edu',
        'courses.christendom.edu',
        'online.christendom.edu',
        'localhost'
    ];
    
    /**
     * Set JWT token as secure cookie
     *
     * @param string $token The JWT token to store
     * @return bool Success status
     * @throws \Exception On cookie setting failure
     */
    public function set_token_cookie(string $token): bool {
        if (!$this->validate_token($token)) {
            throw new \Exception('Invalid token format');
        }

        if (headers_sent($file, $line)) {
            throw new \Exception(
                sprintf('Headers already sent in %s:%s', $file, $line)
            );
        }

        $cookie_name = Settings_Manager::get_cookie_name();
        $domain = $this->get_cookie_domain();
        
        if (!$this->validate_domain($domain)) {
            throw new \Exception('Invalid cookie domain');
        }

        $cookie_options = $this->get_cookie_options($domain);

        try {
            $result = setcookie(
                $cookie_name,
                $token,
                $cookie_options
            );

            if ($result) {
                $_COOKIE[$cookie_name] = $token;
                return true;
            }

            throw new \Exception('Failed to set cookie');
        } catch (\Exception $e) {
            throw new \Exception('Cookie setting error: ' . $e->getMessage());
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
     * Clear JWT token cookie across all paths
     *
     * @return bool Success status
     */
    public function clear_token_cookie(): bool {
        $cookie_name = Settings_Manager::get_cookie_name();
        $paths = ['/', '/wp-admin', '/wp-content', ''];
        $domain = $this->get_cookie_domain();
        
        unset($_COOKIE[$cookie_name]);
        
        if (headers_sent()) {
            return false;
        }

        $base_options = $this->get_cookie_options($domain);
        $clear_options = array_merge($base_options, ['expires' => 1]);

        $success = true;
        foreach ($paths as $path) {
            $clear_options['path'] = $path;
            if (!setcookie($cookie_name, '', $clear_options)) {
                $success = false;
            }
        }

        return $success;
    }

    /**
     * Get secure cookie options
     *
     * @param string $domain Cookie domain
     * @return array Cookie options
     */
    private function get_cookie_options(string $domain): array {
        return [
            'expires' => time() + Settings_Manager::get_cookie_duration(),
            'path' => '/',
            'domain' => '.' . ltrim($domain, '.'),
            'secure' => true,
            'httponly' => Settings_Manager::is_http_only(),
            'samesite' => Settings_Manager::get_samesite_policy()
        ];
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

        // Basic JWT format validation
        $parts = explode('.', $token);
        if (count($parts) !== 3) {
            return false;
        }

        // Validate each part is base64url encoded
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
     * @throws \Exception If domain cannot be determined
     */
    private function get_cookie_domain(): string {
        $site_url = get_site_url();
        $parsed_url = wp_parse_url($site_url);
        
        if (!isset($parsed_url['host'])) {
            throw new \Exception('Unable to determine cookie domain from site URL');
        }

        return $parsed_url['host'];
    }

    /**
     * Validate cookie domain against allowed list
     *
     * @param string $domain Domain to validate
     * @return bool Validation result
     */
    private function validate_domain(string $domain): bool {
        if (empty($domain)) {
            return false;
        }

        // Always allow localhost for development
        if ($domain === 'localhost') {
            return true;
        }

        foreach (self::ALLOWED_DOMAINS as $allowed_domain) {
            if ($domain === $allowed_domain || 
                strpos($domain, '.' . $allowed_domain) !== false) {
                return true;
            }
        }

        return false;
    }
}