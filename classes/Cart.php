<?php
/**
 * Database & Session-Synced Shopping Cart Model
 */

class Cart {
    private Database $db;
    private ?int $userId;
    private ?int $cartId = null;

    public function __construct(?int $userId = null) {
        $this->db = Database::getInstance();
        $this->userId = $userId ?: Auth::id();
        $this->initializeCart();
    }

    private function initializeCart(): void {
        if ($this->userId) {
            $cart = $this->db->fetchOne("SELECT id, coupon_id FROM cart WHERE user_id = :uid", [':uid' => $this->userId]);
            if ($cart) {
                $this->cartId = (int)$cart['id'];
            } else {
                $this->cartId = $this->db->insert('cart', ['user_id' => $this->userId]);
            }

            // Sync session cart items into DB cart if any exist before login
            $this->mergeSessionCart();
        }
    }

    private function mergeSessionCart(): void {
        if (!empty($_SESSION['guest_cart']) && is_array($_SESSION['guest_cart']) && $this->cartId) {
            foreach ($_SESSION['guest_cart'] as $productId => $qty) {
                $this->addItem((int)$productId, (int)$qty);
            }
            unset($_SESSION['guest_cart']);
        }
    }

    public function addItem(int $productId, int $quantity = 1): array {
        $productModel = new Product();
        $product = $productModel->findById($productId, true);

        if (!$product) {
            return ['success' => false, 'message' => 'Product not found or unavailable.'];
        }

        if ($product['stock_quantity'] < 1) {
            return ['success' => false, 'message' => 'Sorry, this product is currently out of stock.'];
        }

        if ($this->userId && $this->cartId) {
            $existing = $this->db->fetchOne(
                "SELECT id, quantity FROM cart_items WHERE cart_id = :cid AND product_id = :pid",
                [':cid' => $this->cartId, ':pid' => $productId]
            );

            $newQty = $quantity;
            if ($existing) {
                $newQty = (int)$existing['quantity'] + $quantity;
            }

            if ($newQty > $product['stock_quantity']) {
                $newQty = $product['stock_quantity'];
            }

            if ($existing) {
                $this->db->update('cart_items', ['quantity' => $newQty], 'id = :id', [':id' => $existing['id']]);
            } else {
                $this->db->insert('cart_items', [
                    'cart_id'    => $this->cartId,
                    'product_id' => $productId,
                    'quantity'   => $newQty,
                ]);
            }
        } else {
            // Guest Session Cart
            if (!isset($_SESSION['guest_cart'])) {
                $_SESSION['guest_cart'] = [];
            }
            $currentQty = $_SESSION['guest_cart'][$productId] ?? 0;
            $newQty = min($currentQty + $quantity, $product['stock_quantity']);
            $_SESSION['guest_cart'][$productId] = $newQty;
        }

        return ['success' => true, 'message' => 'Product added to cart successfully.'];
    }

    public function updateItem(int $productId, int $quantity): array {
        if ($quantity <= 0) {
            return $this->removeItem($productId);
        }

        $productModel = new Product();
        $product = $productModel->findById($productId, true);

        if (!$product) {
            return ['success' => false, 'message' => 'Product not found.'];
        }

        if ($quantity > $product['stock_quantity']) {
            $quantity = $product['stock_quantity'];
            $warning = "Quantity adjusted to maximum available stock ({$product['stock_quantity']}).";
        }

        if ($this->userId && $this->cartId) {
            $this->db->update(
                'cart_items',
                ['quantity' => $quantity],
                'cart_id = :cid AND product_id = :pid',
                [':cid' => $this->cartId, ':pid' => $productId]
            );
        } else {
            $_SESSION['guest_cart'][$productId] = $quantity;
        }

        return ['success' => true, 'message' => $warning ?? 'Cart updated successfully.'];
    }

    public function removeItem(int $productId): array {
        if ($this->userId && $this->cartId) {
            $this->db->delete(
                'cart_items',
                'cart_id = :cid AND product_id = :pid',
                [':cid' => $this->cartId, ':pid' => $productId]
            );
        } else {
            unset($_SESSION['guest_cart'][$productId]);
        }
        return ['success' => true, 'message' => 'Item removed from cart.'];
    }

    public function clear(): void {
        if ($this->userId && $this->cartId) {
            $this->db->delete('cart_items', 'cart_id = :cid', [':cid' => $this->cartId]);
            $this->db->update('cart', ['coupon_id' => null], 'id = :id', [':id' => $this->cartId]);
        } else {
            unset($_SESSION['guest_cart']);
            unset($_SESSION['applied_coupon_id']);
        }
    }

    public function getDetails(): array {
        $items = [];
        $rawItems = [];

        if ($this->userId && $this->cartId) {
            $sql = "SELECT ci.id as cart_item_id, ci.quantity, p.*, c.name as category_name,
                           (SELECT image_path FROM product_images pi WHERE pi.product_id = p.id ORDER BY pi.is_primary DESC LIMIT 1) as primary_image
                    FROM cart_items ci
                    JOIN products p ON ci.product_id = p.id
                    LEFT JOIN categories c ON p.category_id = c.id
                    WHERE ci.cart_id = :cid";
            $rawItems = $this->db->fetchAll($sql, [':cid' => $this->cartId]);
        } else {
            $guestCart = $_SESSION['guest_cart'] ?? [];
            if (!empty($guestCart)) {
                $pids = array_keys($guestCart);
                $placeholders = implode(',', array_fill(0, count($pids), '?'));
                $sql = "SELECT p.*, c.name as category_name,
                               (SELECT image_path FROM product_images pi WHERE pi.product_id = p.id ORDER BY pi.is_primary DESC LIMIT 1) as primary_image
                        FROM products p
                        LEFT JOIN categories c ON p.category_id = c.id
                        WHERE p.id IN ($placeholders)";
                $products = $this->db->fetchAll($sql, array_values($pids));
                foreach ($products as $p) {
                    $p['quantity'] = $guestCart[$p['id']] ?? 1;
                    $rawItems[] = $p;
                }
            }
        }

        $subtotal = 0.00;
        $totalOfferDiscount = 0.00;
        $offerModel = new Offer();

        foreach ($rawItems as $item) {
            $unitPrice = !empty($item['sale_price']) && $item['sale_price'] > 0 ? (float)$item['sale_price'] : (float)$item['price'];
            $offerDiscountPerUnit = $offerModel->calculateItemDiscount($item, $unitPrice);
            $effectiveUnitPrice = max(0, $unitPrice - $offerDiscountPerUnit);
            $itemSubtotal = $effectiveUnitPrice * (int)$item['quantity'];

            $subtotal += $itemSubtotal;
            $totalOfferDiscount += ($offerDiscountPerUnit * (int)$item['quantity']);

            $items[] = [
                'id'                   => (int)$item['id'],
                'name'                 => $item['name'],
                'slug'                 => $item['slug'],
                'sku'                  => $item['sku'],
                'image'                => $item['primary_image'],
                'category_name'        => $item['category_name'] ?? 'General',
                'original_price'       => (float)$item['price'],
                'base_unit_price'      => $unitPrice,
                'offer_discount_unit'  => $offerDiscountPerUnit,
                'effective_unit_price' => $effectiveUnitPrice,
                'quantity'             => (int)$item['quantity'],
                'stock_quantity'       => (int)$item['stock_quantity'],
                'is_in_stock'          => (int)$item['stock_quantity'] >= (int)$item['quantity'],
                'line_total'           => $itemSubtotal,
            ];
        }

        // Coupon Handling
        $couponId = null;
        if ($this->userId && $this->cartId) {
            $cartRow = $this->db->fetchOne("SELECT coupon_id FROM cart WHERE id = :id", [':id' => $this->cartId]);
            $couponId = $cartRow['coupon_id'] ?? null;
        } else {
            $couponId = $_SESSION['applied_coupon_id'] ?? null;
        }

        $couponDiscount = 0.00;
        $couponData = null;
        if ($couponId) {
            $couponModel = new Coupon();
            $c = $couponModel->findById((int)$couponId);
            if ($c) {
                $validation = $couponModel->validate($c['code'], $subtotal, $this->userId);
                if ($validation['valid']) {
                    $couponDiscount = $validation['discount_amount'];
                    $couponData = $validation['coupon'];
                } else {
                    // Invalidate coupon
                    $this->removeCoupon();
                }
            }
        }

        // Shipping Calculation
        $shippingInside = (float) getSetting('shipping_inside_city', '60.00');
        $freeShippingThreshold = (float) getSetting('free_shipping_threshold', '5000.00');
        $shippingFee = ($subtotal >= $freeShippingThreshold || empty($items)) ? 0.00 : $shippingInside;

        $totalAmount = max(0, ($subtotal - $couponDiscount) + $shippingFee);

        return [
            'items'                 => $items,
            'item_count'            => array_sum(array_column($items, 'quantity')),
            // Pre-discount sum of line items. `subtotal` below is already net of
            // offer discounts, so the UI needs this to show arithmetic that reconciles:
            // items_subtotal - offer_discount - coupon_discount + shipping = total_amount
            'items_subtotal'        => round($subtotal + $totalOfferDiscount, 2),
            'subtotal'              => round($subtotal, 2),
            'offer_discount'        => round($totalOfferDiscount, 2),
            'coupon'                => $couponData,
            'coupon_discount'       => round($couponDiscount, 2),
            'shipping_fee'          => round($shippingFee, 2),
            'free_shipping_min'     => $freeShippingThreshold,
            'is_free_shipping'      => $subtotal >= $freeShippingThreshold && !empty($items),
            'total_amount'          => round($totalAmount, 2),
        ];
    }

    public function applyCoupon(string $code): array {
        $details = $this->getDetails();
        if (empty($details['items'])) {
            return ['success' => false, 'message' => 'Cannot apply coupon to an empty cart.'];
        }

        $couponModel = new Coupon();
        $result = $couponModel->validate($code, $details['subtotal'], $this->userId);

        if (!$result['valid']) {
            return ['success' => false, 'message' => $result['message']];
        }

        $coupon = $result['coupon'];
        if ($this->userId && $this->cartId) {
            $this->db->update('cart', ['coupon_id' => $coupon['id']], 'id = :id', [':id' => $this->cartId]);
        } else {
            $_SESSION['applied_coupon_id'] = (int)$coupon['id'];
        }

        return ['success' => true, 'message' => 'Coupon applied successfully!', 'discount' => $result['discount_amount']];
    }

    public function removeCoupon(): void {
        if ($this->userId && $this->cartId) {
            $this->db->update('cart', ['coupon_id' => null], 'id = :id', [':id' => $this->cartId]);
        } else {
            unset($_SESSION['applied_coupon_id']);
        }
    }
}
