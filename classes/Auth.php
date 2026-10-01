<?php
/**
 * Authentication and Role Authorization Engine
 */

class Auth {
    public static function check(): bool {
        return !empty($_SESSION['user_id']);
    }

    public static function user(): ?array {
        if (!self::check()) {
            return null;
        }

        static $cachedUser = null;
        if ($cachedUser !== null && $cachedUser['id'] == $_SESSION['user_id']) {
            return $cachedUser;
        }

        $db = Database::getInstance();
        $user = $db->fetchOne("SELECT id, first_name, last_name, email, phone, role, avatar, is_active FROM users WHERE id = :id", [
            ':id' => $_SESSION['user_id']
        ]);

        if (!$user || !$user['is_active']) {
            self::logout();
            return null;
        }

        $cachedUser = $user;
        return $user;
    }

    public static function id(): ?int {
        return $_SESSION['user_id'] ?? null;
    }

    public static function role(): string {
        return $_SESSION['user_role'] ?? 'guest';
    }

    public static function name(): string {
        return $_SESSION['user_name'] ?? 'User';
    }

    public static function email(): string {
        return $_SESSION['user_email'] ?? '';
    }

    public static function login(string $email, string $password): array {
        $db = Database::getInstance();
        $user = $db->fetchOne("SELECT * FROM users WHERE email = :email", [':email' => trim($email)]);

        if (!$user) {
            return ['success' => false, 'message' => 'Invalid email or password.'];
        }

        if (!$user['is_active']) {
            return ['success' => false, 'message' => 'Your account has been deactivated. Please contact support.'];
        }

        if (!password_verify($password, $user['password_hash'])) {
            return ['success' => false, 'message' => 'Invalid email or password.'];
        }

        // Rehash password if algorithm has updated
        if (password_needs_rehash($user['password_hash'], PASSWORD_DEFAULT)) {
            $newHash = password_hash($password, PASSWORD_DEFAULT);
            $db->update('users', ['password_hash' => $newHash], 'id = :id', [':id' => $user['id']]);
        }

        // Regenerate session to prevent session fixation
        session_regenerate_id(true);

        $_SESSION['user_id'] = (int) $user['id'];
        $_SESSION['user_role'] = $user['role'];
        $_SESSION['user_name'] = $user['first_name'] . ' ' . $user['last_name'];
        $_SESSION['user_email'] = $user['email'];
        $_SESSION['last_activity'] = time();

        // Update last login timestamp
        $db->update('users', ['last_login_at' => date('Y-m-d H:i:s')], 'id = :id', [':id' => $user['id']]);

        return ['success' => true, 'user' => $user];
    }

    public static function register(array $data): array {
        $validator = new Validator($data);
        $isValid = $validator->validate([
            'first_name' => 'required|max:100',
            'last_name'  => 'required|max:100',
            'email'      => 'required|email|max:255|unique:users,email',
            'phone'      => 'phone|max:20',
            'password'   => 'required|min:6|max:100',
            'password_confirmation' => 'required|matches:password',
        ]);

        if (!$isValid) {
            return ['success' => false, 'errors' => $validator->getErrors()];
        }

        $db = Database::getInstance();
        $passwordHash = password_hash($data['password'], PASSWORD_DEFAULT);

        $userId = $db->insert('users', [
            'first_name'    => trim($data['first_name']),
            'last_name'     => trim($data['last_name']),
            'email'         => trim(strtolower($data['email'])),
            'phone'         => !empty($data['phone']) ? trim($data['phone']) : null,
            'password_hash' => $passwordHash,
            'role'          => 'customer',
            'is_active'     => 1,
            'email_verified'=> 0,
        ]);

        // Auto login on successful registration
        self::login($data['email'], $data['password']);

        return ['success' => true, 'user_id' => $userId];
    }

    public static function logout(): void {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $_SESSION = [];

        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000,
                $params["path"], $params["domain"],
                $params["secure"], $params["httponly"]
            );
        }

        session_destroy();
    }

    public static function hasRole(string|array $roles): bool {
        if (!self::check()) return false;
        $userRole = self::role();
        if (is_array($roles)) {
            return in_array($userRole, $roles, true);
        }
        return $userRole === $roles;
    }

    public static function isAdminOrStaff(): bool {
        return self::hasRole(['staff', 'admin', 'owner']);
    }

    public static function isAdminOrOwner(): bool {
        return self::hasRole(['admin', 'owner']);
    }

    public static function isOwner(): bool {
        return self::hasRole('owner');
    }

    public static function can(string $permission): bool {
        if (!self::check()) return false;
        $role = self::role();

        $matrix = [
            'staff' => [
                'view_dashboard', 'manage_products', 'manage_categories',
                'manage_inventory', 'manage_orders', 'manage_banners',
                'manage_reviews'
            ],
            'admin' => [
                'view_dashboard', 'manage_products', 'manage_categories',
                'manage_inventory', 'manage_orders', 'manage_banners',
                'manage_reviews', 'manage_offers', 'manage_coupons',
                'manage_customers', 'view_reports', 'view_logs'
            ],
            'owner' => [
                'view_dashboard', 'manage_products', 'manage_categories',
                'manage_inventory', 'manage_orders', 'manage_banners',
                'manage_reviews', 'manage_offers', 'manage_coupons',
                'manage_customers', 'view_reports', 'view_logs',
                'manage_staff', 'manage_settings', 'full_control'
            ],
        ];

        if ($role === 'owner') return true;
        return in_array($permission, $matrix[$role] ?? [], true);
    }
}
