<?php
namespace ChristendomSSO;

class Cookie_Manager {
    public const COOKIE_NAME = 'christendom_jwt';
    private const COOKIE_DURATION = 3600;

    public function set_token_cookie(string $token): bool {
        if (headers_sent($file, $line)) {
            error_log("Headers already sent in $file:$line");
            return false;
        }

        return setcookie(self::COOKIE_NAME, $token, [
            'expires' => time() + self::COOKIE_DURATION,
            'path' => '/',
            'secure' => true,
            'httponly' => true,
            'samesite' => 'Lax'
        ]);
    }

    public function get_token_cookie(): ?string {
        return $_COOKIE[self::COOKIE_NAME] ?? null;
    }

    public function clear_token_cookie(): bool {
        // Always unset the cookie from $_COOKIE
        unset($_COOKIE[self::COOKIE_NAME]);
        
        // Try multiple paths to ensure cookie is cleared
        $paths = ['/', '/wp-admin', '/wp-content', ''];
        
        foreach ($paths as $path) {
            if (!headers_sent()) {
                setcookie(self::COOKIE_NAME, '', [
                    'expires' => time() - 3600,
                    'path' => $path,
                    'secure' => true,
                    'httponly' => true,
                    'samesite' => 'Lax'
                ]);
            }
        }
        
        error_log("Cookie clear attempted for paths: " . implode(', ', $paths));
        return true;
    }
}