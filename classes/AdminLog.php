<?php
/**
 * Admin Activity Logger
 */

class AdminLog {
    public static function log(string $action, string $entityType, ?int $entityId = null, ?string $description = null): void {
        try {
            $userId = Auth::id() ?? 1;
            $db = Database::getInstance();

            $ip = $_SERVER['HTTP_X_FORWARDED_FOR'] ?? $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
            $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown';

            $db->insert('admin_logs', [
                'user_id'     => $userId,
                'action'      => strtoupper($action),
                'entity_type' => strtolower($entityType),
                'entity_id'   => $entityId,
                'description' => $description,
                'ip_address'  => substr($ip, 0, 45),
                'user_agent'  => substr($userAgent, 0, 500),
            ]);
        } catch (Exception $e) {
            error_log("Failed to write admin log: " . $e->getMessage());
        }
    }

    public static function getRecent(int $limit = 50, int $offset = 0): array {
        $db = Database::getInstance();
        return $db->fetchAll(
            "SELECT l.*, u.first_name, u.last_name, u.email, u.role 
             FROM admin_logs l 
             LEFT JOIN users u ON l.user_id = u.id 
             ORDER BY l.created_at DESC 
             LIMIT :limit OFFSET :offset",
            [':limit' => $limit, ':offset' => $offset]
        );
    }
}
