<?php
/**
 * Promotional Offers & Flash Sale Engine
 */

class Offer {
    private Database $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    public function getAll(bool $onlyActive = false): array {
        $where = ["1=1"];
        $params = [];

        if ($onlyActive) {
            $now = date('Y-m-d H:i:s');
            $where[] = "o.is_active = 1";
            $where[] = "o.start_date <= :now1";
            $where[] = "o.end_date >= :now2";
            $where[] = "(o.usage_limit IS NULL OR o.times_used < o.usage_limit)";
            $params[':now1'] = $now;
            $params[':now2'] = $now;
        }

        $whereClause = implode(' AND ', $where);

        $sql = "SELECT o.*, u.first_name as creator_name,
                       (SELECT COUNT(*) FROM offer_products op WHERE op.offer_id = o.id) as product_count,
                       (SELECT COUNT(*) FROM offer_categories oc WHERE oc.offer_id = o.id) as category_count
                FROM offers o
                LEFT JOIN users u ON o.created_by = u.id
                WHERE $whereClause
                ORDER BY o.is_flash_sale DESC, o.start_date DESC";

        return $this->db->fetchAll($sql, $params);
    }

    public function findById(int $id): ?array {
        $offer = $this->db->fetchOne("SELECT * FROM offers WHERE id = :id", [':id' => $id]);
        if ($offer) {
            $offer['products'] = $this->db->fetchAll("SELECT p.id, p.name, p.price, p.sku FROM offer_products op JOIN products p ON op.product_id = p.id WHERE op.offer_id = :id", [':id' => $id]);
            $offer['categories'] = $this->db->fetchAll("SELECT c.id, c.name FROM offer_categories oc JOIN categories c ON oc.category_id = c.id WHERE oc.offer_id = :id", [':id' => $id]);
        }
        return $offer;
    }

    public function getActiveFlashSale(): ?array {
        $now = date('Y-m-d H:i:s');
        $sql = "SELECT o.* FROM offers o 
                WHERE o.is_flash_sale = 1 AND o.is_active = 1 
                  AND o.start_date <= :now1 AND o.end_date >= :now2 
                ORDER BY o.end_date ASC LIMIT 1";
        $offer = $this->db->fetchOne($sql, [':now1' => $now, ':now2' => $now]);
        if ($offer) {
            $offer['products'] = $this->db->fetchAll(
                "SELECT p.*, c.name as category_name,
                        (SELECT image_path FROM product_images pi WHERE pi.product_id = p.id ORDER BY pi.is_primary DESC LIMIT 1) as primary_image
                 FROM offer_products op 
                 JOIN products p ON op.product_id = p.id 
                 LEFT JOIN categories c ON p.category_id = c.id
                 WHERE op.offer_id = :id AND p.is_active = 1",
                [':id' => $offer['id']]
            );
        }
        return $offer;
    }

    public function calculateItemDiscount(array $product, float $itemPrice): float {
        $now = date('Y-m-d H:i:s');

        // Check product specific offer
        $sql = "SELECT o.* FROM offers o 
                JOIN offer_products op ON o.id = op.offer_id 
                WHERE op.product_id = :pid AND o.is_active = 1 
                  AND o.start_date <= :now1 AND o.end_date >= :now2 
                  AND (o.usage_limit IS NULL OR o.times_used < o.usage_limit)
                ORDER BY o.discount_value DESC LIMIT 1";
        $offer = $this->db->fetchOne($sql, [':pid' => $product['id'], ':now1' => $now, ':now2' => $now]);

        // If no product offer, check category offer
        if (!$offer && !empty($product['category_id'])) {
            $sql = "SELECT o.* FROM offers o 
                    JOIN offer_categories oc ON o.id = oc.offer_id 
                    WHERE oc.category_id = :cid AND o.is_active = 1 
                      AND o.start_date <= :now1 AND o.end_date >= :now2 
                      AND (o.usage_limit IS NULL OR o.times_used < o.usage_limit)
                    ORDER BY o.discount_value DESC LIMIT 1";
            $offer = $this->db->fetchOne($sql, [':cid' => $product['category_id'], ':now1' => $now, ':now2' => $now]);
        }

        if (!$offer) return 0.00;

        $discount = 0.00;
        if ($offer['discount_type'] === 'percentage') {
            $discount = ($itemPrice * (float)$offer['discount_value']) / 100.0;
        } else {
            $discount = (float)$offer['discount_value'];
        }

        if (!empty($offer['max_discount_amount']) && $discount > (float)$offer['max_discount_amount']) {
            $discount = (float)$offer['max_discount_amount'];
        }

        return min($discount, $itemPrice);
    }

    public function create(array $data): int {
        $offerId = $this->db->insert('offers', [
            'name'                => trim($data['name']),
            'description'         => !empty($data['description']) ? trim($data['description']) : null,
            'discount_type'       => $data['discount_type'],
            'discount_value'      => (float)$data['discount_value'],
            'min_order_amount'    => !empty($data['min_order_amount']) ? (float)$data['min_order_amount'] : null,
            'max_discount_amount' => !empty($data['max_discount_amount']) ? (float)$data['max_discount_amount'] : null,
            'start_date'          => $data['start_date'],
            'end_date'            => $data['end_date'],
            'usage_limit'         => !empty($data['usage_limit']) ? (int)$data['usage_limit'] : null,
            'per_user_limit'      => !empty($data['per_user_limit']) ? (int)$data['per_user_limit'] : null,
            'is_flash_sale'       => !empty($data['is_flash_sale']) ? 1 : 0,
            'flash_stock'         => !empty($data['flash_stock']) ? (int)$data['flash_stock'] : null,
            'is_active'           => isset($data['is_active']) ? (int)$data['is_active'] : 1,
            'created_by'          => Auth::id(),
        ]);

        if (!empty($data['product_ids']) && is_array($data['product_ids'])) {
            foreach ($data['product_ids'] as $pid) {
                if (!empty($pid)) {
                    $this->db->insert('offer_products', ['offer_id' => $offerId, 'product_id' => (int)$pid]);
                }
            }
        }

        if (!empty($data['category_ids']) && is_array($data['category_ids'])) {
            foreach ($data['category_ids'] as $cid) {
                if (!empty($cid)) {
                    $this->db->insert('offer_categories', ['offer_id' => $offerId, 'category_id' => (int)$cid]);
                }
            }
        }

        AdminLog::log('OFFER_CREATED', 'offer', $offerId, "Created offer: {$data['name']}");
        return $offerId;
    }

    public function update(int $id, array $data): bool {
        $offer = $this->findById($id);
        if (!$offer) return false;

        $this->db->update('offers', [
            'name'                => trim($data['name']),
            'description'         => !empty($data['description']) ? trim($data['description']) : null,
            'discount_type'       => $data['discount_type'],
            'discount_value'      => (float)$data['discount_value'],
            'min_order_amount'    => !empty($data['min_order_amount']) ? (float)$data['min_order_amount'] : null,
            'max_discount_amount' => !empty($data['max_discount_amount']) ? (float)$data['max_discount_amount'] : null,
            'start_date'          => $data['start_date'],
            'end_date'            => $data['end_date'],
            'usage_limit'         => !empty($data['usage_limit']) ? (int)$data['usage_limit'] : null,
            'per_user_limit'      => !empty($data['per_user_limit']) ? (int)$data['per_user_limit'] : null,
            'is_flash_sale'       => !empty($data['is_flash_sale']) ? 1 : 0,
            'flash_stock'         => !empty($data['flash_stock']) ? (int)$data['flash_stock'] : null,
            'is_active'           => isset($data['is_active']) ? (int)$data['is_active'] : 1,
        ], 'id = :id', [':id' => $id]);

        // Sync products
        $this->db->delete('offer_products', 'offer_id = :id', [':id' => $id]);
        if (!empty($data['product_ids']) && is_array($data['product_ids'])) {
            foreach ($data['product_ids'] as $pid) {
                if (!empty($pid)) {
                    $this->db->insert('offer_products', ['offer_id' => $id, 'product_id' => (int)$pid]);
                }
            }
        }

        // Sync categories
        $this->db->delete('offer_categories', 'offer_id = :id', [':id' => $id]);
        if (!empty($data['category_ids']) && is_array($data['category_ids'])) {
            foreach ($data['category_ids'] as $cid) {
                if (!empty($cid)) {
                    $this->db->insert('offer_categories', ['offer_id' => $id, 'category_id' => (int)$cid]);
                }
            }
        }

        AdminLog::log('OFFER_UPDATED', 'offer', $id, "Updated offer: {$data['name']}");
        return true;
    }

    public function delete(int $id): bool {
        $offer = $this->findById($id);
        if (!$offer) return false;

        $this->db->delete('offers', 'id = :id', [':id' => $id]);
        AdminLog::log('OFFER_DELETED', 'offer', $id, "Deleted offer: {$offer['name']}");
        return true;
    }
}
