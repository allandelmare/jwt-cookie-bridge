<?php
namespace ChristendomSSO;

class Cookie_Manager {
    private const COOKIE_DURATION = 3600;

    public function set_token_cookie(string $token): bool {
        if (headers_sent($file, $line)) {
            error_log("Headers already sent in $file:$line");
            return false;
        }

        return setcookie(Settings_Manager::get_cookie_name(), $token, [
            'expires' => time() + self::COOKIE_DURATION,
            'path' => '/',
            'secure' => true,
            'httponly' => Settings_Manager::is_http_only(),
            'samesite' => Settings_Manager::get_samesite_policy()
        ]);
    }

    public function get_token_cookie(): ?string {
        return $_COOKIE[Settings_Manager::get_cookie_name()] ?? null;
    }

    public function clear_token_cookie(): bool {
        $cookie_name = Settings_Manager::get_cookie_name();
        
        // Always unset the cookie from $_COOKIE
        unset($_COOKIE[$cookie_name]);
        
        // Try multiple paths to ensure cookie is cleared
        $paths = ['/', '/wp-admin', '/wp-content', ''];
        
        foreach ($paths as $path) {
            if (!headers_sent()) {
                setcookie($cookie_name, '', [
                    'expires' => time() - 3600,
                    'path' => $path,
                    'secure' => true,
                    'httponly' => Settings_Manager::is_http_only(),
                    'samesite' => Settings_Manager::get_samesite_policy()
                ]);
            }
        }
        
        error_log("Cookie clear attempted for paths: " . implode(', ', $paths));
        return true;
    }
}