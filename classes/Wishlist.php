<?php
/**
 * Wishlist Model
 */

class Wishlist {
    private Database $db;
    private ?int $userId;
    private ?int $wishlistId = null;

    public function __construct(?int $userId = null) {
        $this->db = Database::getInstance();
        $this->userId = $userId ?: Auth::id();
        $this->initializeWishlist();
    }

    private function initializeWishlist(): void {
        if ($this->userId) {
            $wishlist = $this->db->fetchOne("SELECT id FROM wishlist WHERE user_id = :uid", [':uid' => $this->userId]);
            if ($wishlist) {
                $this->wishlistId = (int)$wishlist['id'];
            } else {
                $this->wishlistId = $this->db->insert('wishlist', ['user_id' => $this->userId]);
            }
        }
    }

    public function getItems(): array {
        if (!$this->userId || !$this->wishlistId) {
            return [];
        }

        $sql = "SELECT wi.id as wishlist_item_id, wi.created_at as added_at, p.*, c.name as category_name,
                       (SELECT image_path FROM product_images pi WHERE pi.product_id = p.id ORDER BY pi.is_primary DESC LIMIT 1) as primary_image
                FROM wishlist_items wi
                JOIN products p ON wi.product_id = p.id
                LEFT JOIN categories c ON p.category_id = c.id
                WHERE wi.wishlist_id = :wid AND p.is_active = 1
                ORDER BY wi.created_at DESC";

        return $this->db->fetchAll($sql, [':wid' => $this->wishlistId]);
    }

    public function add(int $productId): array {
        if (!$this->userId || !$this->wishlistId) {
            return ['success' => false, 'message' => 'Please login to save items to your wishlist.', 'require_login' => true];
        }

        $existing = $this->db->fetchOne(
            "SELECT id FROM wishlist_items WHERE wishlist_id = :wid AND product_id = :pid",
            [':wid' => $this->wishlistId, ':pid' => $productId]
        );

        if ($existing) {
            return ['success' => true, 'message' => 'Item is already in your wishlist.'];
        }

        $this->db->insert('wishlist_items', [
            'wishlist_id' => $this->wishlistId,
            'product_id'  => $productId,
        ]);

        return ['success' => true, 'message' => 'Added to your wishlist!'];
    }

    public function remove(int $productId): array {
        if (!$this->userId || !$this->wishlistId) {
            return ['success' => false, 'message' => 'Unauthorized'];
        }

        $this->db->delete(
            'wishlist_items',
            'wishlist_id = :wid AND product_id = :pid',
            [':wid' => $this->wishlistId, ':pid' => $productId]
        );

        return ['success' => true, 'message' => 'Removed from wishlist.'];
    }

    public function isInWishlist(int $productId): bool {
        if (!$this->userId || !$this->wishlistId) return false;
        return (int) $this->db->fetchColumn(
            "SELECT COUNT(*) FROM wishlist_items WHERE wishlist_id = :wid AND product_id = :pid",
            [':wid' => $this->wishlistId, ':pid' => $productId]
        ) > 0;
    }

    public function moveToCart(int $productId): array {
        $cart = new Cart($this->userId);
        $res = $cart->addItem($productId, 1);

        if ($res['success']) {
            $this->remove($productId);
        }

        return $res;
    }
}
