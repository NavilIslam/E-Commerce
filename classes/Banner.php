<?php
/**
 * Banner and Dynamic CMS Section Model
 */

class Banner {
    private Database $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    public function getActiveBanners(string $position = 'hero'): array {
        $now = date('Y-m-d H:i:s');
        $sql = "SELECT * FROM banners 
                WHERE position = :pos AND is_active = 1 
                  AND (start_date IS NULL OR start_date <= :now1) 
                  AND (end_date IS NULL OR end_date >= :now2)
                ORDER BY sort_order ASC, created_at DESC";
        return $this->db->fetchAll($sql, [':pos' => $position, ':now1' => $now, ':now2' => $now]);
    }

    public function getAll(): array {
        return $this->db->fetchAll(
            "SELECT b.*, u.first_name as creator_name 
             FROM banners b 
             LEFT JOIN users u ON b.created_by = u.id 
             ORDER BY b.position ASC, b.sort_order ASC"
        );
    }

    public function findById(int $id): ?array {
        return $this->db->fetchOne("SELECT * FROM banners WHERE id = :id", [':id' => $id]);
    }

    public function create(array $data): int {
        $id = $this->db->insert('banners', [
            'title'       => !empty($data['title']) ? trim($data['title']) : null,
            'subtitle'    => !empty($data['subtitle']) ? trim($data['subtitle']) : null,
            'image'       => $data['image'],
            'button_text' => !empty($data['button_text']) ? trim($data['button_text']) : null,
            'button_url'  => !empty($data['button_url']) ? trim($data['button_url']) : null,
            'position'    => $data['position'] ?? 'hero',
            'sort_order'  => (int)($data['sort_order'] ?? 0),
            'start_date'  => !empty($data['start_date']) ? $data['start_date'] : null,
            'end_date'    => !empty($data['end_date']) ? $data['end_date'] : null,
            'is_active'   => isset($data['is_active']) ? (int)$data['is_active'] : 1,
            'created_by'  => Auth::id(),
        ]);

        AdminLog::log('BANNER_CREATED', 'banner', $id, "Created banner: " . ($data['title'] ?? "ID $id"));
        return $id;
    }

    public function update(int $id, array $data): bool {
        $banner = $this->findById($id);
        if (!$banner) return false;

        $updateData = [
            'title'       => !empty($data['title']) ? trim($data['title']) : null,
            'subtitle'    => !empty($data['subtitle']) ? trim($data['subtitle']) : null,
            'button_text' => !empty($data['button_text']) ? trim($data['button_text']) : null,
            'button_url'  => !empty($data['button_url']) ? trim($data['button_url']) : null,
            'position'    => $data['position'] ?? 'hero',
            'sort_order'  => (int)($data['sort_order'] ?? 0),
            'start_date'  => !empty($data['start_date']) ? $data['start_date'] : null,
            'end_date'    => !empty($data['end_date']) ? $data['end_date'] : null,
            'is_active'   => isset($data['is_active']) ? (int)$data['is_active'] : 1,
        ];

        if (!empty($data['image'])) {
            $updateData['image'] = $data['image'];
        }

        $this->db->update('banners', $updateData, 'id = :id', [':id' => $id]);
        AdminLog::log('BANNER_UPDATED', 'banner', $id, "Updated banner ID $id");
        return true;
    }

    public function delete(int $id): bool {
        $banner = $this->findById($id);
        if (!$banner) return false;

        $filePath = UPLOAD_PATH . '/banners/' . $banner['image'];
        if (file_exists($filePath)) @unlink($filePath);

        $this->db->delete('banners', 'id = :id', [':id' => $id]);
        AdminLog::log('BANNER_DELETED', 'banner', $id, "Deleted banner ID $id");
        return true;
    }

    // Homepage Section Methods
    public function getHomepageSections(): array {
        $sections = $this->db->fetchAll("SELECT * FROM homepage_sections WHERE is_active = 1 ORDER BY sort_order ASC");
        foreach ($sections as &$sec) {
            $sql = "SELECT p.*, c.name as category_name,
                           (SELECT image_path FROM product_images pi WHERE pi.product_id = p.id ORDER BY pi.is_primary DESC LIMIT 1) as primary_image
                    FROM homepage_section_items hsi
                    JOIN products p ON hsi.product_id = p.id
                    LEFT JOIN categories c ON p.category_id = c.id
                    WHERE hsi.section_id = :sid AND p.is_active = 1
                    ORDER BY hsi.sort_order ASC
                    LIMIT :limit";
            $sec['products'] = $this->db->fetchAll($sql, [':sid' => $sec['id'], ':limit' => (int)$sec['max_items']]);
        }
        return $sections;
    }
}
