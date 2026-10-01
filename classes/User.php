<?php
/**
 * User & Customer Management Model
 */

class User {
    private Database $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    public function findById(int $id): ?array {
        return $this->db->fetchOne("SELECT id, first_name, last_name, email, phone, role, avatar, is_active, email_verified, created_at, last_login_at FROM users WHERE id = :id", [':id' => $id]);
    }

    public function findByEmail(string $email): ?array {
        return $this->db->fetchOne("SELECT * FROM users WHERE email = :email", [':email' => trim($email)]);
    }

    public function getAll(array $filters = [], int $page = 1, int $perPage = 20): array {
        $where = ["1=1"];
        $params = [];

        if (!empty($filters['role'])) {
            $where[] = "role = :role";
            $params[':role'] = $filters['role'];
        }

        if (!empty($filters['search'])) {
            // One placeholder per column: emulated prepares are disabled, so a
            // named placeholder cannot be reused within a statement.
            $where[] = "(first_name LIKE :q1 OR last_name LIKE :q2 OR email LIKE :q3 OR phone LIKE :q4)";
            $term = '%' . $filters['search'] . '%';
            $params[':q1'] = $term;
            $params[':q2'] = $term;
            $params[':q3'] = $term;
            $params[':q4'] = $term;
        }

        if (isset($filters['is_active']) && $filters['is_active'] !== '') {
            $where[] = "is_active = :is_active";
            $params[':is_active'] = (int)$filters['is_active'];
        }

        $whereClause = implode(' AND ', $where);

        $total = (int) $this->db->fetchColumn("SELECT COUNT(*) FROM users WHERE $whereClause", $params);
        $offset = ($page - 1) * $perPage;

        $sql = "SELECT u.id, u.first_name, u.last_name, u.email, u.phone, u.role, u.is_active, u.created_at, u.last_login_at,
                       COUNT(o.id) as total_orders,
                       COALESCE(SUM(CASE WHEN o.order_status != 'cancelled' THEN o.total_amount ELSE 0 END), 0) as total_spent
                FROM users u
                LEFT JOIN orders o ON u.id = o.user_id
                WHERE $whereClause
                GROUP BY u.id
                ORDER BY u.created_at DESC
                LIMIT :limit OFFSET :offset";

        $params[':limit'] = $perPage;
        $params[':offset'] = $offset;

        $users = $this->db->fetchAll($sql, $params);

        return [
            'users'       => $users,
            'total'       => $total,
            'page'        => $page,
            'per_page'    => $perPage,
            'total_pages' => ceil($total / $perPage),
        ];
    }

    public function updateProfile(int $id, array $data): bool {
        $updateData = [
            'first_name' => trim($data['first_name']),
            'last_name'  => trim($data['last_name']),
            'phone'      => !empty($data['phone']) ? trim($data['phone']) : null,
        ];

        if (!empty($data['avatar'])) {
            $updateData['avatar'] = $data['avatar'];
        }

        $updated = $this->db->update('users', $updateData, 'id = :id', [':id' => $id]);

        if (isset($_SESSION['user_id']) && $_SESSION['user_id'] == $id) {
            $_SESSION['user_name'] = $updateData['first_name'] . ' ' . $updateData['last_name'];
        }

        return $updated >= 0;
    }

    public function updatePassword(int $id, string $newPassword): bool {
        $hash = password_hash($newPassword, PASSWORD_DEFAULT);
        return $this->db->update('users', ['password_hash' => $hash], 'id = :id', [':id' => $id]) > 0;
    }

    public function toggleStatus(int $id): bool {
        $user = $this->findById($id);
        if (!$user) return false;

        $newStatus = $user['is_active'] ? 0 : 1;
        $this->db->update('users', ['is_active' => $newStatus], 'id = :id', [':id' => $id]);
        AdminLog::log('USER_STATUS_TOGGLE', 'user', $id, "Changed user {$user['email']} status to " . ($newStatus ? 'active' : 'inactive'));
        return true;
    }

    public function updateRole(int $id, string $role): bool {
        if (!in_array($role, ['customer', 'staff', 'admin', 'owner'])) return false;
        $this->db->update('users', ['role' => $role], 'id = :id', [':id' => $id]);
        AdminLog::log('USER_ROLE_CHANGED', 'user', $id, "Updated user role to $role");
        return true;
    }

    // Addresses
    public function getAddresses(int $userId): array {
        return $this->db->fetchAll("SELECT * FROM addresses WHERE user_id = :uid ORDER BY is_default DESC, created_at DESC", [':uid' => $userId]);
    }

    public function getDefaultAddress(int $userId): ?array {
        $addr = $this->db->fetchOne("SELECT * FROM addresses WHERE user_id = :uid AND is_default = 1", [':uid' => $userId]);
        if (!$addr) {
            $addr = $this->db->fetchOne("SELECT * FROM addresses WHERE user_id = :uid ORDER BY created_at DESC LIMIT 1", [':uid' => $userId]);
        }
        return $addr;
    }

    public function saveAddress(int $userId, array $data, ?int $addressId = null): int {
        if (!empty($data['is_default'])) {
            $this->db->update('addresses', ['is_default' => 0], 'user_id = :uid', [':uid' => $userId]);
        }

        $addressData = [
            'user_id'       => $userId,
            'label'         => $data['label'] ?? 'Home',
            'full_name'     => trim($data['full_name']),
            'phone'         => trim($data['phone']),
            'address_line1' => trim($data['address_line1']),
            'address_line2' => !empty($data['address_line2']) ? trim($data['address_line2']) : null,
            'city'          => trim($data['city']),
            'area'          => !empty($data['area']) ? trim($data['area']) : null,
            'postal_code'   => !empty($data['postal_code']) ? trim($data['postal_code']) : null,
            'country'       => $data['country'] ?? 'Bangladesh',
            'is_default'    => !empty($data['is_default']) ? 1 : 0,
        ];

        if ($addressId) {
            $this->db->update('addresses', $addressData, 'id = :id AND user_id = :uid', [':id' => $addressId, ':uid' => $userId]);
            return $addressId;
        }

        return $this->db->insert('addresses', $addressData);
    }

    public function deleteAddress(int $userId, int $addressId): bool {
        return $this->db->delete('addresses', 'id = :id AND user_id = :uid', [':id' => $addressId, ':uid' => $userId]) > 0;
    }
}
