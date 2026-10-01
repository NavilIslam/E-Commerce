<?php
/**
 * Product Review & Rating System Model
 */

class Review {
    private Database $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    public function getProductReviews(int $productId, bool $onlyApproved = true): array {
        $where = "r.product_id = :pid";
        if ($onlyApproved) {
            $where .= " AND r.status = 'approved'";
        }

        $sql = "SELECT r.*, u.first_name, u.last_name, u.avatar,
                       CASE WHEN r.order_id IS NOT NULL THEN 1 ELSE 0 END as is_verified_buyer
                FROM reviews r
                JOIN users u ON r.user_id = u.id
                WHERE $where
                ORDER BY r.created_at DESC";

        return $this->db->fetchAll($sql, [':pid' => $productId]);
    }

    public function canUserReview(int $userId, int $productId): array {
        // Check if user already reviewed
        $existing = $this->db->fetchOne("SELECT id, status FROM reviews WHERE user_id = :uid AND product_id = :pid", [
            ':uid' => $userId,
            ':pid' => $productId,
        ]);

        if ($existing) {
            return ['allowed' => false, 'message' => 'You have already submitted a review for this product.'];
        }

        // Verify if user purchased this product in a completed/delivered order
        $order = $this->db->fetchOne(
            "SELECT o.id FROM orders o 
             JOIN order_items oi ON o.id = oi.order_id 
             WHERE o.user_id = :uid AND oi.product_id = :pid AND o.order_status IN ('delivered', 'shipped', 'processing', 'pending')
             LIMIT 1",
            [':uid' => $userId, ':pid' => $productId]
        );

        return [
            'allowed'  => true,
            'order_id' => $order ? (int)$order['id'] : null,
            'is_buyer' => (bool)$order,
        ];
    }

    public function create(int $userId, int $productId, int $rating, string $comment): array {
        $canReview = $this->canUserReview($userId, $productId);
        if (!$canReview['allowed']) {
            return ['success' => false, 'message' => $canReview['message']];
        }

        $rating = max(1, min(5, $rating));

        $reviewId = $this->db->insert('reviews', [
            'user_id'    => $userId,
            'product_id' => $productId,
            'order_id'   => $canReview['order_id'],
            'rating'     => $rating,
            'comment'    => trim($comment),
            'status'     => 'approved', // Auto-approved for verified/good flow (or moderate via admin)
        ]);

        $this->recalculateProductRating($productId);

        return ['success' => true, 'message' => 'Thank you! Your review has been submitted successfully.'];
    }

    public function getAll(array $filters = [], int $page = 1, int $perPage = 20): array {
        $where = ["1=1"];
        $params = [];

        if (!empty($filters['status'])) {
            $where[] = "r.status = :status";
            $params[':status'] = $filters['status'];
        }

        if (!empty($filters['rating'])) {
            $where[] = "r.rating = :rating";
            $params[':rating'] = (int)$filters['rating'];
        }

        if (!empty($filters['search'])) {
            // One placeholder per column: emulated prepares are disabled, so a
            // named placeholder cannot be reused within a statement.
            $where[] = "(p.name LIKE :q1 OR u.first_name LIKE :q2 OR u.last_name LIKE :q3 OR r.comment LIKE :q4)";
            $term = '%' . $filters['search'] . '%';
            $params[':q1'] = $term;
            $params[':q2'] = $term;
            $params[':q3'] = $term;
            $params[':q4'] = $term;
        }

        $whereClause = implode(' AND ', $where);

        $total = (int) $this->db->fetchColumn(
            "SELECT COUNT(*) FROM reviews r 
             JOIN users u ON r.user_id = u.id 
             JOIN products p ON r.product_id = p.id 
             WHERE $whereClause",
            $params
        );

        $offset = ($page - 1) * $perPage;

        $reviews = $this->db->fetchAll(
            "SELECT r.*, u.first_name, u.last_name, u.email as user_email, p.name as product_name, p.slug as product_slug
             FROM reviews r
             JOIN users u ON r.user_id = u.id
             JOIN products p ON r.product_id = p.id
             WHERE $whereClause
             ORDER BY r.created_at DESC
             LIMIT :limit OFFSET :offset",
            array_merge($params, [':limit' => $perPage, ':offset' => $offset])
        );

        return [
            'reviews'     => $reviews,
            'total'       => $total,
            'page'        => $page,
            'per_page'    => $perPage,
            'total_pages' => ceil($total / $perPage),
        ];
    }

    public function updateStatus(int $reviewId, string $status): bool {
        if (!in_array($status, ['approved', 'hidden', 'deleted', 'pending'])) return false;

        $review = $this->db->fetchOne("SELECT * FROM reviews WHERE id = :id", [':id' => $reviewId]);
        if (!$review) return false;

        $this->db->update('reviews', ['status' => $status], 'id = :id', [':id' => $reviewId]);
        $this->recalculateProductRating((int)$review['product_id']);

        AdminLog::log('REVIEW_STATUS_UPDATED', 'review', $reviewId, "Updated review #$reviewId status to $status");
        return true;
    }

    public function delete(int $reviewId): bool {
        $review = $this->db->fetchOne("SELECT * FROM reviews WHERE id = :id", [':id' => $reviewId]);
        if (!$review) return false;

        $this->db->delete('reviews', 'id = :id', [':id' => $reviewId]);
        $this->recalculateProductRating((int)$review['product_id']);

        AdminLog::log('REVIEW_DELETED', 'review', $reviewId, "Deleted review #$reviewId");
        return true;
    }

    public function recalculateProductRating(int $productId): void {
        $stats = $this->db->fetchOne(
            "SELECT COUNT(*) as cnt, COALESCE(AVG(rating), 0) as avg_r 
             FROM reviews 
             WHERE product_id = :pid AND status = 'approved'",
            [':pid' => $productId]
        );

        $this->db->update('products', [
            'review_count' => (int)($stats['cnt'] ?? 0),
            'avg_rating'   => round((float)($stats['avg_r'] ?? 0), 2),
        ], 'id = :id', [':id' => $productId]);
    }
}
