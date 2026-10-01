<?php
/**
 * Category Model
 */

class Category {
    private Database $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    public function getAll(bool $onlyActive = true): array {
        $where = $onlyActive ? "WHERE c.is_active = 1" : "";
        // `preview_image` falls back to a real photo of a product in the category
        // when no dedicated category image has been uploaded, so category cards
        // are always image-led rather than showing a generic placeholder.
        $sql = "SELECT c.*,
                       (SELECT COUNT(*) FROM products p WHERE p.category_id = c.id AND p.is_active = 1) as product_count,
                       (SELECT pi.image_path
                          FROM products p
                          JOIN product_images pi ON pi.product_id = p.id
                         WHERE p.category_id = c.id AND p.is_active = 1
                      ORDER BY p.is_featured DESC, pi.is_primary DESC, p.id ASC
                         LIMIT 1) as preview_image,
                       parent.name as parent_name
                FROM categories c
                LEFT JOIN categories parent ON c.parent_id = parent.id
                $where
                ORDER BY c.sort_order ASC, c.name ASC";
        return $this->db->fetchAll($sql);
    }

    public function findById(int $id): ?array {
        return $this->db->fetchOne("SELECT * FROM categories WHERE id = :id", [':id' => $id]);
    }

    public function findBySlug(string $slug): ?array {
        return $this->db->fetchOne("SELECT * FROM categories WHERE slug = :slug AND is_active = 1", [':slug' => $slug]);
    }

    public function create(array $data): int {
        $slug = !empty($data['slug']) ? slugify($data['slug']) : slugify($data['name']);

        // Ensure unique slug
        $originalSlug = $slug;
        $counter = 1;
        while ($this->db->fetchColumn("SELECT COUNT(*) FROM categories WHERE slug = :slug", [':slug' => $slug]) > 0) {
            $slug = $originalSlug . '-' . $counter++;
        }

        $id = $this->db->insert('categories', [
            'name'        => trim($data['name']),
            'slug'        => $slug,
            'description' => !empty($data['description']) ? trim($data['description']) : null,
            'image'       => !empty($data['image']) ? $data['image'] : null,
            'parent_id'   => !empty($data['parent_id']) ? (int)$data['parent_id'] : null,
            'sort_order'  => (int)($data['sort_order'] ?? 0),
            'is_active'   => isset($data['is_active']) ? (int)$data['is_active'] : 1,
        ]);

        AdminLog::log('CATEGORY_CREATED', 'category', $id, "Created category: {$data['name']}");
        return $id;
    }

    public function update(int $id, array $data): bool {
        $category = $this->findById($id);
        if (!$category) return false;

        $slug = !empty($data['slug']) ? slugify($data['slug']) : slugify($data['name']);

        // Ensure unique slug except current
        $originalSlug = $slug;
        $counter = 1;
        while ($this->db->fetchColumn("SELECT COUNT(*) FROM categories WHERE slug = :slug AND id != :id", [':slug' => $slug, ':id' => $id]) > 0) {
            $slug = $originalSlug . '-' . $counter++;
        }

        $updateData = [
            'name'        => trim($data['name']),
            'slug'        => $slug,
            'description' => !empty($data['description']) ? trim($data['description']) : null,
            'parent_id'   => !empty($data['parent_id']) && (int)$data['parent_id'] !== $id ? (int)$data['parent_id'] : null,
            'sort_order'  => (int)($data['sort_order'] ?? 0),
            'is_active'   => isset($data['is_active']) ? (int)$data['is_active'] : 1,
        ];

        if (!empty($data['image'])) {
            $updateData['image'] = $data['image'];
        }

        $this->db->update('categories', $updateData, 'id = :id', [':id' => $id]);
        AdminLog::log('CATEGORY_UPDATED', 'category', $id, "Updated category: {$data['name']}");
        return true;
    }

    public function delete(int $id): bool {
        $category = $this->findById($id);
        if (!$category) return false;

        // Unlink products before deletion
        $this->db->update('products', ['category_id' => null], 'category_id = :cid', [':cid' => $id]);
        // Set parent_id null for child categories
        $this->db->update('categories', ['parent_id' => null], 'parent_id = :cid', [':cid' => $id]);

        $this->db->delete('categories', 'id = :id', [':id' => $id]);
        AdminLog::log('CATEGORY_DELETED', 'category', $id, "Deleted category: {$category['name']}");
        return true;
    }

    public function toggleStatus(int $id): bool {
        $cat = $this->findById($id);
        if (!$cat) return false;

        $newStatus = $cat['is_active'] ? 0 : 1;
        $this->db->update('categories', ['is_active' => $newStatus], 'id = :id', [':id' => $id]);
        AdminLog::log('CATEGORY_STATUS_TOGGLED', 'category', $id, "Toggled status to " . ($newStatus ? 'active' : 'inactive'));
        return true;
    }
}
