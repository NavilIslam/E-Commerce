<?php
/**
 * CSRF Protection Manager
 */

class CSRF {
    public static function getToken(): string {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }

    public static function validate(?string $token = null): bool {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if ($token === null) {
            // Check POST parameter first
            if (isset($_POST['csrf_token'])) {
                $token = $_POST['csrf_token'];
            }
            // Check HTTP headers (X-CSRF-TOKEN or X-Requested-With)
            elseif (isset($_SERVER['HTTP_X_CSRF_TOKEN'])) {
                $token = $_SERVER['HTTP_X_CSRF_TOKEN'];
            }
        }

        if (empty($token) || empty($_SESSION['csrf_token'])) {
            return false;
        }

        return hash_equals($_SESSION['csrf_token'], $token);
    }

    public static function requireValid(): void {
        if (!self::validate()) {
            if (!empty($_SERVER['HTTP_ACCEPT']) && str_contains($_SERVER['HTTP_ACCEPT'], 'application/json')) {
                jsonError('Invalid or expired security token (CSRF). Please refresh and try again.', [], 403);
            }
            http_response_code(403);
            die('Invalid or expired security token (CSRF). Please return to the previous page and try again.');
        }
    }
}
