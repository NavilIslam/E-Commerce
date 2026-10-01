<?php
/**
 * Product & Inventory Model
 */

class Product {
    private Database $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    public function getAll(array $filters = [], int $page = 1, int $perPage = 12): array {
        $where = ["1=1"];
        $params = [];

        // Public/Admin active status
        if (isset($filters['is_active'])) {
            $where[] = "p.is_active = :is_active";
            $params[':is_active'] = (int)$filters['is_active'];
        }

        // Category filter (id or slug)
        if (!empty($filters['category_id'])) {
            $where[] = "p.category_id = :cat_id";
            $params[':cat_id'] = (int)$filters['category_id'];
        } elseif (!empty($filters['category'])) {
            $where[] = "c.slug = :cat_slug";
            $params[':cat_slug'] = $filters['category'];
        }

        // Brand filter
        if (!empty($filters['brand'])) {
            $where[] = "p.brand = :brand";
            $params[':brand'] = $filters['brand'];
        }

        // Featured filter
        if (isset($filters['featured'])) {
            $where[] = "p.is_featured = :featured";
            $params[':featured'] = (int)$filters['featured'];
        }

        // Price range
        if (isset($filters['min_price']) && is_numeric($filters['min_price'])) {
            $where[] = "COALESCE(p.sale_price, p.price) >= :min_price";
            $params[':min_price'] = (float)$filters['min_price'];
        }
        if (isset($filters['max_price']) && is_numeric($filters['max_price'])) {
            $where[] = "COALESCE(p.sale_price, p.price) <= :max_price";
            $params[':max_price'] = (float)$filters['max_price'];
        }

        // On Sale filter
        if (!empty($filters['on_sale'])) {
            $where[] = "p.sale_price IS NOT NULL AND p.sale_price < p.price";
        }

        // Availability filter
        if (!empty($filters['in_stock'])) {
            $where[] = "p.stock_quantity > 0";
        } elseif (isset($filters['stock_status'])) {
            if ($filters['stock_status'] === 'in_stock') {
                $where[] = "p.stock_quantity > p.low_stock_threshold";
            } elseif ($filters['stock_status'] === 'low_stock') {
                $where[] = "p.stock_quantity > 0 AND p.stock_quantity <= p.low_stock_threshold";
            } elseif ($filters['stock_status'] === 'out_of_stock') {
                $where[] = "p.stock_quantity = 0";
            }
        }

        // Search query.
        // Each column needs its own placeholder: emulated prepares are disabled,
        // and real prepared statements reject a named placeholder used more than once.
        if (!empty($filters['search'])) {
            $where[] = "(p.name LIKE :q1 OR p.brand LIKE :q2 OR p.sku LIKE :q3 OR p.description LIKE :q4)";
            $term = '%' . $filters['search'] . '%';
            $params[':q1'] = $term;
            $params[':q2'] = $term;
            $params[':q3'] = $term;
            $params[':q4'] = $term;
        }

        // Rating filter
        if (!empty($filters['min_rating'])) {
            $where[] = "p.avg_rating >= :min_rating";
            $params[':min_rating'] = (float)$filters['min_rating'];
        }

        $whereClause = implode(' AND ', $where);

        // Sorting
        $orderBy = "p.created_at DESC";
        $sort = $filters['sort'] ?? 'newest';
        switch ($sort) {
            case 'price_low':
                $orderBy = "COALESCE(p.sale_price, p.price) ASC";
                break;
            case 'price_high':
                $orderBy = "COALESCE(p.sale_price, p.price) DESC";
                break;
            case 'popular':
                $orderBy = "p.total_sold DESC, p.view_count DESC";
                break;
            case 'rating':
                $orderBy = "p.avg_rating DESC, p.review_count DESC";
                break;
            case 'name_asc':
                $orderBy = "p.name ASC";
                break;
            case 'newest':
            default:
                $orderBy = "p.created_at DESC";
                break;
        }

        // Count Total
        $countSql = "SELECT COUNT(*) FROM products p 
                     LEFT JOIN categories c ON p.category_id = c.id 
                     WHERE $whereClause";
        $total = (int) $this->db->fetchColumn($countSql, $params);

        $offset = ($page - 1) * $perPage;

        // Fetch Records
        $sql = "SELECT p.*, c.name as category_name, c.slug as category_slug,
                       (SELECT image_path FROM product_images pi WHERE pi.product_id = p.id ORDER BY pi.is_primary DESC, pi.sort_order ASC LIMIT 1) as primary_image
                FROM products p
                LEFT JOIN categories c ON p.category_id = c.id
                WHERE $whereClause
                ORDER BY $orderBy
                LIMIT :limit OFFSET :offset";

        $params[':limit'] = $perPage;
        $params[':offset'] = $offset;

        $products = $this->db->fetchAll($sql, $params);

        return [
            'products'    => $products,
            'total'       => $total,
            'page'        => $page,
            'per_page'    => $perPage,
            'total_pages' => ceil($total / $perPage),
        ];
    }

    public function findById(int $id, bool $onlyActive = false): ?array {
        $where = "p.id = :id";
        if ($onlyActive) $where .= " AND p.is_active = 1";

        $sql = "SELECT p.*, c.name as category_name, c.slug as category_slug,
                       (SELECT image_path FROM product_images pi WHERE pi.product_id = p.id ORDER BY pi.is_primary DESC, pi.sort_order ASC LIMIT 1) as primary_image
                FROM products p
                LEFT JOIN categories c ON p.category_id = c.id
                WHERE $where";

        $product = $this->db->fetchOne($sql, [':id' => $id]);
        if ($product) {
            $product['images'] = $this->getImages($product['id']);
        }
        return $product;
    }

    public function findBySlug(string $slug): ?array {
        $sql = "SELECT p.*, c.name as category_name, c.slug as category_slug,
                       (SELECT image_path FROM product_images pi WHERE pi.product_id = p.id ORDER BY pi.is_primary DESC, pi.sort_order ASC LIMIT 1) as primary_image
                FROM products p
                LEFT JOIN categories c ON p.category_id = c.id
                WHERE p.slug = :slug AND p.is_active = 1";

        $product = $this->db->fetchOne($sql, [':slug' => $slug]);
        if ($product) {
            $product['images'] = $this->getImages($product['id']);
            $this->incrementViews($product['id']);
        }
        return $product;
    }

    public function incrementViews(int $productId): void {
        $this->db->query("UPDATE products SET view_count = view_count + 1 WHERE id = :id", [':id' => $productId]);
    }

    public function getImages(int $productId): array {
        return $this->db->fetchAll("SELECT * FROM product_images WHERE product_id = :pid ORDER BY is_primary DESC, sort_order ASC, id ASC", [':pid' => $productId]);
    }

    public function addImage(int $productId, string $imagePath, string $altText = '', bool $isPrimary = false): int {
        if ($isPrimary) {
            $this->db->update('product_images', ['is_primary' => 0], 'product_id = :pid', [':pid' => $productId]);
        }
        return $this->db->insert('product_images', [
            'product_id' => $productId,
            'image_path' => $imagePath,
            'alt_text'   => $altText,
            'is_primary' => $isPrimary ? 1 : 0,
            'sort_order' => 0,
        ]);
    }

    public function deleteImage(int $imageId): bool {
        $img = $this->db->fetchOne("SELECT * FROM product_images WHERE id = :id", [':id' => $imageId]);
        if (!$img) return false;

        $filePath = UPLOAD_PATH . '/products/' . $img['image_path'];
        if (file_exists($filePath)) {
            @unlink($filePath);
        }

        return $this->db->delete('product_images', 'id = :id', [':id' => $imageId]) > 0;
    }

    public function create(array $data): int {
        $slug = !empty($data['slug']) ? slugify($data['slug']) : slugify($data['name']);

        $originalSlug = $slug;
        $counter = 1;
        while ($this->db->fetchColumn("SELECT COUNT(*) FROM products WHERE slug = :slug", [':slug' => $slug]) > 0) {
            $slug = $originalSlug . '-' . $counter++;
        }

        $id = $this->db->insert('products', [
            'category_id'         => !empty($data['category_id']) ? (int)$data['category_id'] : null,
            'name'                => trim($data['name']),
            'slug'                => $slug,
            'description'         => !empty($data['description']) ? trim($data['description']) : null,
            'short_description'   => !empty($data['short_description']) ? trim($data['short_description']) : null,
            'sku'                 => !empty($data['sku']) ? trim($data['sku']) : 'SKU-' . strtoupper(bin2hex(random_bytes(4))),
            'brand'               => !empty($data['brand']) ? trim($data['brand']) : null,
            'price'               => (float)$data['price'],
            'sale_price'          => (!empty($data['sale_price']) && (float)$data['sale_price'] > 0) ? (float)$data['sale_price'] : null,
            'stock_quantity'      => (int)($data['stock_quantity'] ?? 0),
            'low_stock_threshold' => (int)($data['low_stock_threshold'] ?? 5),
            'weight'              => !empty($data['weight']) ? (float)$data['weight'] : null,
            'is_active'           => isset($data['is_active']) ? (int)$data['is_active'] : 1,
            'is_featured'         => isset($data['is_featured']) ? (int)$data['is_featured'] : 0,
        ]);

        AdminLog::log('PRODUCT_CREATED', 'product', $id, "Created product: {$data['name']}");
        return $id;
    }

    public function update(int $id, array $data): bool {
        $product = $this->findById($id);
        if (!$product) return false;

        $slug = !empty($data['slug']) ? slugify($data['slug']) : slugify($data['name']);

        $originalSlug = $slug;
        $counter = 1;
        while ($this->db->fetchColumn("SELECT COUNT(*) FROM products WHERE slug = :slug AND id != :id", [':slug' => $slug, ':id' => $id]) > 0) {
            $slug = $originalSlug . '-' . $counter++;
        }

        $updateData = [
            'category_id'         => !empty($data['category_id']) ? (int)$data['category_id'] : null,
            'name'                => trim($data['name']),
            'slug'                => $slug,
            'description'         => !empty($data['description']) ? trim($data['description']) : null,
            'short_description'   => !empty($data['short_description']) ? trim($data['short_description']) : null,
            'sku'                 => !empty($data['sku']) ? trim($data['sku']) : $product['sku'],
            'brand'               => !empty($data['brand']) ? trim($data['brand']) : null,
            'price'               => (float)$data['price'],
            'sale_price'          => (!empty($data['sale_price']) && (float)$data['sale_price'] > 0) ? (float)$data['sale_price'] : null,
            'stock_quantity'      => (int)($data['stock_quantity'] ?? 0),
            'low_stock_threshold' => (int)($data['low_stock_threshold'] ?? 5),
            'weight'              => !empty($data['weight']) ? (float)$data['weight'] : null,
            'is_active'           => isset($data['is_active']) ? (int)$data['is_active'] : 1,
            'is_featured'         => isset($data['is_featured']) ? (int)$data['is_featured'] : 0,
        ];

        $this->db->update('products', $updateData, 'id = :id', [':id' => $id]);
        AdminLog::log('PRODUCT_UPDATED', 'product', $id, "Updated product: {$data['name']}");
        return true;
    }

    public function updateStock(int $id, int $newStock): bool {
        $this->db->update('products', ['stock_quantity' => max(0, $newStock)], 'id = :id', [':id' => $id]);
        AdminLog::log('STOCK_UPDATED', 'product', $id, "Adjusted stock quantity to $newStock");
        return true;
    }

    public function delete(int $id): bool {
        $product = $this->findById($id);
        if (!$product) return false;

        // Check if product was ordered
        $orderCount = (int) $this->db->fetchColumn("SELECT COUNT(*) FROM order_items WHERE product_id = :pid", [':pid' => $id]);
        if ($orderCount > 0) {
            // Soft delete/deactivate to preserve historical order integrity
            $this->db->update('products', ['is_active' => 0], 'id = :id', [':id' => $id]);
            AdminLog::log('PRODUCT_DEACTIVATED', 'product', $id, "Deactivated product due to existing order history: {$product['name']}");
            return true;
        }

        // Clean up images
        $images = $this->getImages($id);
        foreach ($images as $img) {
            $filePath = UPLOAD_PATH . '/products/' . $img['image_path'];
            if (file_exists($filePath)) @unlink($filePath);
        }

        $this->db->delete('products', 'id = :id', [':id' => $id]);
        AdminLog::log('PRODUCT_DELETED', 'product', $id, "Deleted product: {$product['name']}");
        return true;
    }

    public function getBrands(): array {
        return $this->db->fetchAll("SELECT DISTINCT brand FROM products WHERE brand IS NOT NULL AND brand != '' AND is_active = 1 ORDER BY brand ASC");
    }

    public function getRelatedProducts(int $productId, ?int $categoryId, int $limit = 4): array {
        if (!$categoryId) {
            return [];
        }
        $sql = "SELECT p.*, c.name as category_name,
                       (SELECT image_path FROM product_images pi WHERE pi.product_id = p.id ORDER BY pi.is_primary DESC LIMIT 1) as primary_image
                FROM products p
                LEFT JOIN categories c ON p.category_id = c.id
                WHERE p.category_id = :cid AND p.id != :pid AND p.is_active = 1
                ORDER BY p.total_sold DESC, p.created_at DESC
                LIMIT :limit";
        return $this->db->fetchAll($sql, [':cid' => $categoryId, ':pid' => $productId, ':limit' => $limit]);
    }
}
