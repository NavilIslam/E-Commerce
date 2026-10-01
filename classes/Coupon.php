<?php
/**
 * Coupon Management and Validation Model
 */

class Coupon {
    private Database $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    public function getAll(): array {
        return $this->db->fetchAll(
            "SELECT c.*, u.first_name as creator_name,
                    (SELECT COUNT(*) FROM coupon_usage cu WHERE cu.coupon_id = c.id) as real_usage_count
             FROM coupons c
             LEFT JOIN users u ON c.created_by = u.id
             ORDER BY c.created_at DESC"
        );
    }

    public function findById(int $id): ?array {
        return $this->db->fetchOne("SELECT * FROM coupons WHERE id = :id", [':id' => $id]);
    }

    public function findByCode(string $code): ?array {
        return $this->db->fetchOne("SELECT * FROM coupons WHERE code = :code", [':code' => strtoupper(trim($code))]);
    }

    public function validate(string $code, float $orderSubtotal, ?int $userId = null): array {
        $code = strtoupper(trim($code));
        $coupon = $this->findByCode($code);

        if (!$coupon) {
            return ['valid' => false, 'message' => 'Invalid coupon code.'];
        }

        if (!$coupon['is_active']) {
            return ['valid' => false, 'message' => 'This coupon is currently inactive.'];
        }

        $now = date('Y-m-d H:i:s');
        if ($coupon['start_date'] > $now) {
            return ['valid' => false, 'message' => 'This coupon offer has not started yet.'];
        }

        if ($coupon['end_date'] < $now) {
            return ['valid' => false, 'message' => 'This coupon has expired.'];
        }

        if ($coupon['usage_limit'] !== null && $coupon['times_used'] >= $coupon['usage_limit']) {
            return ['valid' => false, 'message' => 'This coupon has reached its maximum total usage limit.'];
        }

        if ($coupon['min_order_amount'] !== null && $orderSubtotal < (float)$coupon['min_order_amount']) {
            return ['valid' => false, 'message' => 'Minimum order amount for this coupon is ' . formatPrice($coupon['min_order_amount']) . '.'];
        }

        // Per user usage check
        if ($userId) {
            $userUsage = (int) $this->db->fetchColumn(
                "SELECT COUNT(*) FROM coupon_usage WHERE coupon_id = :cid AND user_id = :uid",
                [':cid' => $coupon['id'], ':uid' => $userId]
            );
            if ($userUsage >= $coupon['per_user_limit']) {
                return ['valid' => false, 'message' => 'You have already utilized this coupon code the maximum allowed times.'];
            }
        }

        // Calculate discount
        $discount = 0.00;
        if ($coupon['discount_type'] === 'percentage') {
            $discount = ($orderSubtotal * (float)$coupon['discount_value']) / 100.0;
        } else {
            $discount = (float)$coupon['discount_value'];
        }

        if ($coupon['max_discount'] !== null && $discount > (float)$coupon['max_discount']) {
            $discount = (float)$coupon['max_discount'];
        }

        $discount = min($discount, $orderSubtotal);

        return [
            'valid'           => true,
            'coupon'          => $coupon,
            'discount_amount' => round($discount, 2),
            'message'         => 'Coupon applied successfully!',
        ];
    }

    public function recordUsage(int $couponId, int $userId, int $orderId, float $discountAmount): void {
        $this->db->insert('coupon_usage', [
            'coupon_id'       => $couponId,
            'user_id'         => $userId,
            'order_id'        => $orderId,
            'discount_amount' => $discountAmount,
        ]);

        $this->db->query("UPDATE coupons SET times_used = times_used + 1 WHERE id = :id", [':id' => $couponId]);
    }

    public function create(array $data): int {
        $code = strtoupper(trim($data['code']));
        $id = $this->db->insert('coupons', [
            'code'             => $code,
            'description'      => !empty($data['description']) ? trim($data['description']) : null,
            'discount_type'    => $data['discount_type'],
            'discount_value'   => (float)$data['discount_value'],
            'min_order_amount' => !empty($data['min_order_amount']) ? (float)$data['min_order_amount'] : null,
            'max_discount'     => !empty($data['max_discount']) ? (float)$data['max_discount'] : null,
            'start_date'       => $data['start_date'],
            'end_date'         => $data['end_date'],
            'usage_limit'      => !empty($data['usage_limit']) ? (int)$data['usage_limit'] : null,
            'per_user_limit'   => !empty($data['per_user_limit']) ? (int)$data['per_user_limit'] : 1,
            'is_active'        => isset($data['is_active']) ? (int)$data['is_active'] : 1,
            'created_by'       => Auth::id(),
        ]);

        AdminLog::log('COUPON_CREATED', 'coupon', $id, "Created coupon: $code");
        return $id;
    }

    public function update(int $id, array $data): bool {
        $coupon = $this->findById($id);
        if (!$coupon) return false;

        $code = strtoupper(trim($data['code']));
        $this->db->update('coupons', [
            'code'             => $code,
            'description'      => !empty($data['description']) ? trim($data['description']) : null,
            'discount_type'    => $data['discount_type'],
            'discount_value'   => (float)$data['discount_value'],
            'min_order_amount' => !empty($data['min_order_amount']) ? (float)$data['min_order_amount'] : null,
            'max_discount'     => !empty($data['max_discount']) ? (float)$data['max_discount'] : null,
            'start_date'       => $data['start_date'],
            'end_date'         => $data['end_date'],
            'usage_limit'      => !empty($data['usage_limit']) ? (int)$data['usage_limit'] : null,
            'per_user_limit'   => !empty($data['per_user_limit']) ? (int)$data['per_user_limit'] : 1,
            'is_active'        => isset($data['is_active']) ? (int)$data['is_active'] : 1,
        ], 'id = :id', [':id' => $id]);

        AdminLog::log('COUPON_UPDATED', 'coupon', $id, "Updated coupon: $code");
        return true;
    }

    public function delete(int $id): bool {
        $coupon = $this->findById($id);
        if (!$coupon) return false;

        $this->db->delete('coupons', 'id = :id', [':id' => $id]);
        AdminLog::log('COUPON_DELETED', 'coupon', $id, "Deleted coupon: {$coupon['code']}");
        return true;
    }
}
