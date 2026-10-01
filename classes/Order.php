<?php
/**
 * Order Processing & Inventory Transaction Engine
 */

class Order {
    private Database $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    public function createFromCart(int $userId, array $shippingData, string $paymentMethod = 'cod', ?string $notes = null): array {
        $cartModel = new Cart($userId);
        $cartDetails = $cartModel->getDetails();

        if (empty($cartDetails['items'])) {
            return ['success' => false, 'message' => 'Cannot checkout with an empty cart.'];
        }

        // Validate stock for all items before starting transaction
        foreach ($cartDetails['items'] as $item) {
            $currentStock = (int) $this->db->fetchColumn("SELECT stock_quantity FROM products WHERE id = :id AND is_active = 1", [':id' => $item['id']]);
            if ($currentStock < $item['quantity']) {
                return [
                    'success' => false,
                    'message' => "Insufficient stock for '{$item['name']}'. Only {$currentStock} available."
                ];
            }
        }

        // Begin Transaction
        $this->db->beginTransaction();

        try {
            $orderNumber = 'ORD-' . date('Ymd') . '-' . strtoupper(bin2hex(random_bytes(3)));

            // 1. Create Order Record
            $orderId = $this->db->insert('orders', [
                'user_id'          => $userId,
                'order_number'     => $orderNumber,
                'subtotal'         => $cartDetails['subtotal'],
                'discount_amount'  => $cartDetails['offer_discount'],
                'coupon_discount'  => $cartDetails['coupon_discount'],
                'shipping_fee'     => $cartDetails['shipping_fee'],
                'total_amount'     => $cartDetails['total_amount'],
                'coupon_id'        => $cartDetails['coupon']['id'] ?? null,
                'coupon_code'      => $cartDetails['coupon']['code'] ?? null,
                'order_status'     => 'pending',
                'payment_status'   => $paymentMethod === 'cod' ? 'pending' : 'paid',
                'payment_method'   => $paymentMethod,
                'shipping_name'    => trim($shippingData['full_name']),
                'shipping_phone'   => trim($shippingData['phone']),
                'shipping_address' => trim($shippingData['address_line1']) . (!empty($shippingData['address_line2']) ? ', ' . trim($shippingData['address_line2']) : ''),
                'shipping_city'    => trim($shippingData['city']),
                'shipping_area'    => !empty($shippingData['area']) ? trim($shippingData['area']) : null,
                'shipping_postal'  => !empty($shippingData['postal_code']) ? trim($shippingData['postal_code']) : null,
                'notes'            => !empty($notes) ? trim($notes) : null,
            ]);

            // 2. Create Order Items & Decrement Inventory Safely
            foreach ($cartDetails['items'] as $item) {
                $this->db->insert('order_items', [
                    'order_id'        => $orderId,
                    'product_id'      => $item['id'],
                    'product_name'    => $item['name'],
                    'product_sku'     => $item['sku'],
                    'quantity'        => $item['quantity'],
                    'unit_price'      => $item['effective_unit_price'],
                    'discount_amount' => $item['offer_discount_unit'] * $item['quantity'],
                    'total_price'     => $item['line_total'],
                ]);

                // Reduce inventory and increase total_sold
                $this->db->query(
                    "UPDATE products 
                     SET stock_quantity = stock_quantity - :qty,
                         total_sold = total_sold + :qty2
                     WHERE id = :pid AND stock_quantity >= :qty3",
                    [
                        ':qty'  => $item['quantity'],
                        ':qty2' => $item['quantity'],
                        ':pid'  => $item['id'],
                        ':qty3' => $item['quantity'],
                    ]
                );
            }

            // 3. Record Payment
            $paymentStatus = $paymentMethod === 'cod' ? 'pending' : 'completed';
            $transactionId = $paymentMethod === 'cod' ? null : 'TXN-' . strtoupper(bin2hex(random_bytes(6)));

            $this->db->insert('payments', [
                'order_id'         => $orderId,
                'payment_method'   => $paymentMethod,
                'transaction_id'   => $transactionId,
                'amount'           => $cartDetails['total_amount'],
                'currency'         => CURRENCY_CODE,
                'status'           => $paymentStatus,
                'paid_at'          => $paymentStatus === 'completed' ? date('Y-m-d H:i:s') : null,
            ]);

            // 4. Record Coupon Usage if applied
            if (!empty($cartDetails['coupon'])) {
                $couponModel = new Coupon();
                $couponModel->recordUsage($cartDetails['coupon']['id'], $userId, $orderId, $cartDetails['coupon_discount']);
            }

            // 5. Clear Cart
            $cartModel->clear();

            // Commit Transaction
            $this->db->commit();

            return [
                'success'      => true,
                'order_id'     => $orderId,
                'order_number' => $orderNumber,
                'total_amount' => $cartDetails['total_amount'],
                'message'      => 'Your order has been placed successfully!',
            ];
        } catch (Exception $e) {
            $this->db->rollBack();
            error_log("Order placement failed: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'An error occurred while processing your order. Please try again.'
            ];
        }
    }

    public function findById(int $id, ?int $userId = null): ?array {
        $where = "o.id = :id";
        $params = [':id' => $id];

        if ($userId) {
            $where .= " AND o.user_id = :uid";
            $params[':uid'] = $userId;
        }

        $order = $this->db->fetchOne(
            "SELECT o.*, u.first_name, u.last_name, u.email as customer_email, u.phone as customer_phone
             FROM orders o
             JOIN users u ON o.user_id = u.id
             WHERE $where",
            $params
        );

        if ($order) {
            $order['items'] = $this->db->fetchAll(
                "SELECT oi.*, p.slug as product_slug,
                        (SELECT image_path FROM product_images pi WHERE pi.product_id = oi.product_id ORDER BY pi.is_primary DESC LIMIT 1) as product_image
                 FROM order_items oi
                 LEFT JOIN products p ON oi.product_id = p.id
                 WHERE oi.order_id = :oid",
                [':oid' => $order['id']]
            );

            $order['payment'] = $this->db->fetchOne("SELECT * FROM payments WHERE order_id = :oid", [':oid' => $order['id']]);
        }

        return $order;
    }

    public function findByOrderNumber(string $orderNumber, ?int $userId = null): ?array {
        $order = $this->db->fetchOne("SELECT id FROM orders WHERE order_number = :num", [':num' => trim($orderNumber)]);
        if (!$order) return null;
        return $this->findById((int)$order['id'], $userId);
    }

    public function getCustomerOrders(int $userId, int $page = 1, int $perPage = 10): array {
        $total = (int) $this->db->fetchColumn("SELECT COUNT(*) FROM orders WHERE user_id = :uid", [':uid' => $userId]);
        $offset = ($page - 1) * $perPage;

        // `thumbs` lets the order list be image-led, which is the retail
        // convention and makes past orders scannable at a glance.
        $orders = $this->db->fetchAll(
            "SELECT o.*,
                    (SELECT COUNT(*) FROM order_items oi WHERE oi.order_id = o.id) as total_items,
                    (SELECT GROUP_CONCAT(
                                (SELECT pi.image_path FROM product_images pi
                                  WHERE pi.product_id = oi2.product_id
                               ORDER BY pi.is_primary DESC LIMIT 1)
                                ORDER BY oi2.id SEPARATOR ',')
                       FROM order_items oi2
                      WHERE oi2.order_id = o.id) as thumbs
             FROM orders o
             WHERE o.user_id = :uid
             ORDER BY o.created_at DESC
             LIMIT :limit OFFSET :offset",
            [':uid' => $userId, ':limit' => $perPage, ':offset' => $offset]
        );

        return [
            'orders'      => $orders,
            'total'       => $total,
            'page'        => $page,
            'per_page'    => $perPage,
            'total_pages' => ceil($total / $perPage),
        ];
    }

    public function getAll(array $filters = [], int $page = 1, int $perPage = 20): array {
        $where = ["1=1"];
        $params = [];

        if (!empty($filters['order_status'])) {
            $where[] = "o.order_status = :status";
            $params[':status'] = $filters['order_status'];
        }

        if (!empty($filters['payment_status'])) {
            $where[] = "o.payment_status = :pstatus";
            $params[':pstatus'] = $filters['payment_status'];
        }

        if (!empty($filters['search'])) {
            // One placeholder per column: emulated prepares are disabled, so a
            // named placeholder cannot be reused within a statement.
            $where[] = "(o.order_number LIKE :q1 OR o.shipping_name LIKE :q2 OR o.shipping_phone LIKE :q3 OR u.email LIKE :q4)";
            $term = '%' . $filters['search'] . '%';
            $params[':q1'] = $term;
            $params[':q2'] = $term;
            $params[':q3'] = $term;
            $params[':q4'] = $term;
        }

        if (!empty($filters['start_date'])) {
            $where[] = "o.created_at >= :start_date";
            $params[':start_date'] = $filters['start_date'] . ' 00:00:00';
        }

        if (!empty($filters['end_date'])) {
            $where[] = "o.created_at <= :end_date";
            $params[':end_date'] = $filters['end_date'] . ' 23:59:59';
        }

        $whereClause = implode(' AND ', $where);

        $total = (int) $this->db->fetchColumn(
            "SELECT COUNT(*) FROM orders o JOIN users u ON o.user_id = u.id WHERE $whereClause",
            $params
        );

        $offset = ($page - 1) * $perPage;

        $orders = $this->db->fetchAll(
            "SELECT o.*, u.first_name, u.last_name, u.email as customer_email,
                    (SELECT COUNT(*) FROM order_items oi WHERE oi.order_id = o.id) as item_count
             FROM orders o
             JOIN users u ON o.user_id = u.id
             WHERE $whereClause
             ORDER BY o.created_at DESC
             LIMIT :limit OFFSET :offset",
            array_merge($params, [':limit' => $perPage, ':offset' => $offset])
        );

        return [
            'orders'      => $orders,
            'total'       => $total,
            'page'        => $page,
            'per_page'    => $perPage,
            'total_pages' => ceil($total / $perPage),
        ];
    }

    public function updateStatus(int $orderId, string $orderStatus, ?string $paymentStatus = null): bool {
        $allowedStatuses = ['pending', 'processing', 'shipped', 'delivered', 'cancelled', 'refunded'];
        if (!in_array($orderStatus, $allowedStatuses)) return false;

        $updateData = ['order_status' => $orderStatus];
        if ($paymentStatus && in_array($paymentStatus, ['pending', 'paid', 'failed', 'refunded'])) {
            $updateData['payment_status'] = $paymentStatus;
            // Update payment table as well
            $this->db->update('payments', [
                'status' => $paymentStatus === 'paid' ? 'completed' : $paymentStatus,
                'paid_at' => $paymentStatus === 'paid' ? date('Y-m-d H:i:s') : null,
            ], 'order_id = :oid', [':oid' => $orderId]);
        }

        // If order was cancelled, restore inventory
        $current = $this->findById($orderId);
        if ($current && $current['order_status'] !== 'cancelled' && $orderStatus === 'cancelled') {
            foreach ($current['items'] as $item) {
                $this->db->query(
                    "UPDATE products SET stock_quantity = stock_quantity + :qty, total_sold = GREATEST(0, total_sold - :qty2) WHERE id = :pid",
                    [':qty' => $item['quantity'], ':qty2' => $item['quantity'], ':pid' => $item['product_id']]
                );
            }
        }

        $this->db->update('orders', $updateData, 'id = :id', [':id' => $orderId]);
        AdminLog::log('ORDER_STATUS_UPDATED', 'order', $orderId, "Updated order #{$current['order_number']} status to $orderStatus ($paymentStatus)");
        return true;
    }
}
