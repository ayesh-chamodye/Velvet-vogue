<?php
// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Database connection
require_once __DIR__ . '/../config/database.php';

/**
 * Clean input data to prevent XSS attacks
 */
function clean_input($data) {
    global $conn;
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    if ($conn) {
        $data = mysqli_real_escape_string($conn, $data);
    }
    return $data;
}

/**
 * Check if user is logged in
 */
function is_logged_in() {
    return isset($_SESSION['user_id']);
}

/**
 * Check if user is admin
 */
function is_admin() {
    return isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin';
}

/**
 * Redirect to a specific page
 */
function redirect($url) {
    header("Location: $url");
    exit;
}

/**
 * Display flash messages
 */
function display_message() {
    if (isset($_SESSION['message'])) {
        $message = $_SESSION['message'];
        $type = isset($_SESSION['message_type']) ? $_SESSION['message_type'] : 'info';
        
        echo "<div class='alert alert-{$type} alert-dismissible fade show' role='alert'>";
        echo $message;
        echo "<button type='button' class='btn-close' data-bs-dismiss='alert' aria-label='Close'></button>";
        echo "</div>";
        
        unset($_SESSION['message']);
        unset($_SESSION['message_type']);
    }
}

/**
 * Set flash message
 */
function set_message($message, $type = 'info') {
    $_SESSION['message'] = $message;
    $_SESSION['message_type'] = $type;
}

/**
 * Get product by ID
 */
function get_product($product_id) {
    global $conn;
    
    $sql = "SELECT p.*, c.name as category_name 
            FROM products p 
            LEFT JOIN categories c ON p.category_id = c.id 
            WHERE p.id = ?";
    
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "i", $product_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    if ($result && mysqli_num_rows($result) > 0) {
        return mysqli_fetch_assoc($result);
    }
    
    return null;
}

/**
 * Get product images
 */
function get_product_images($product_id) {
    global $conn;
    
    $sql = "SELECT * FROM product_images WHERE product_id = ? ORDER BY is_primary DESC";
    
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "i", $product_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    $images = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $images[] = $row;
    }
    
    return $images;
}

/**
 * Get product attributes
 */
function get_product_attributes($product_id) {
    global $conn;
    
    $sql = "SELECT * FROM product_attributes WHERE product_id = ?";
    
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "i", $product_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    $attributes = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $attributes[] = $row;
    }
    
    return $attributes;
}

/**
 * Get categories
 */
function get_categories() {
    global $conn;
    
    // Check if status column exists in categories table
    $check_column = mysqli_query($conn, "SHOW COLUMNS FROM categories LIKE 'status'");
    
    if(mysqli_num_rows($check_column) > 0) {
        $sql = "SELECT * FROM categories WHERE status = 'active' ORDER BY name";
    } else {
        $sql = "SELECT * FROM categories ORDER BY name";
    }
    
    $result = mysqli_query($conn, $sql);
    
    $categories = [];
    if($result) {
        while ($row = mysqli_fetch_assoc($result)) {
            $categories[] = $row;
        }
    }
    
    return $categories;
}

/**
 * Get featured products
 */
function get_featured_products($limit = 8) {
    global $conn;
    
    $sql = "SELECT p.*, 
            (SELECT image_url FROM product_images WHERE product_id = p.id AND is_primary = 1 LIMIT 1) as primary_image 
            FROM products p 
            WHERE p.featured = 1 AND p.status = 'active' 
            ORDER BY p.created_at DESC 
            LIMIT ?";
    
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "i", $limit);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    $products = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $products[] = $row;
    }
    
    return $products;
}

/**
 * Get products by category
 */
function get_products_by_category($category_id, $limit = 12, $offset = 0) {
    global $conn;
    
    $sql = "SELECT p.*, 
            (SELECT image_url FROM product_images WHERE product_id = p.id AND is_primary = 1 LIMIT 1) as primary_image 
            FROM products p 
            WHERE p.category_id = ? AND p.status = 'active' 
            ORDER BY p.created_at DESC 
            LIMIT ? OFFSET ?";
    
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "iii", $category_id, $limit, $offset);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    $products = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $products[] = $row;
    }
    
    return $products;
}

/**
 * Search products
 */
function search_products($query, $filters = [], $limit = 12, $offset = 0) {
    global $conn;
    
    $sql = "SELECT p.*, 
            (SELECT image_url FROM product_images WHERE product_id = p.id AND is_primary = 1 LIMIT 1) as primary_image 
            FROM products p 
            WHERE p.status = 'active' AND (p.name LIKE ? OR p.description LIKE ?)";
    
    $search_term = "%" . $query . "%";
    
    // Add filters
    if (!empty($filters['category_id'])) {
        $sql .= " AND p.category_id = " . (int)$filters['category_id'];
    }
    
    if (!empty($filters['min_price'])) {
        $sql .= " AND p.price >= " . (float)$filters['min_price'];
    }
    
    if (!empty($filters['max_price'])) {
        $sql .= " AND p.price <= " . (float)$filters['max_price'];
    }
    
    $sql .= " ORDER BY p.created_at DESC LIMIT ? OFFSET ?";
    
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "ssii", $search_term, $search_term, $limit, $offset);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    $products = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $products[] = $row;
    }
    
    return $products;
}

/**
 * Get active banners
 */
function get_active_banners($position = null) {
    global $conn;
    
    $sql = "SELECT * FROM banners WHERE status = 'active' 
            AND (start_date IS NULL OR start_date <= CURDATE()) 
            AND (end_date IS NULL OR end_date >= CURDATE())";
    
    if ($position) {
        $sql .= " AND position = '" . clean_input($position) . "'";
    }
    
    $sql .= " ORDER BY id DESC";
    
    $result = mysqli_query($conn, $sql);
    
    $banners = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $banners[] = $row;
    }
    
    return $banners;
}

/**
 * Format price
 */
function format_price($price) {
    return '$' . number_format($price, 2);
}

/**
 * Add item to cart
 */
function add_to_cart($product_id, $quantity = 1, $attributes = []) {
    if (!isset($_SESSION['cart'])) {
        $_SESSION['cart'] = [];
    }
    
    $product = get_product($product_id);
    if (!$product) {
        return false;
    }
    
    // Generate a unique cart item ID based on product ID and attributes
    $cart_item_id = $product_id;
    if (!empty($attributes)) {
        $cart_item_id .= '_' . md5(json_encode($attributes));
    }
    
    // Check if product already exists in cart
    if (isset($_SESSION['cart'][$cart_item_id])) {
        $_SESSION['cart'][$cart_item_id]['quantity'] += $quantity;
    } else {
        $_SESSION['cart'][$cart_item_id] = [
            'product_id' => $product_id,
            'name' => $product['name'],
            'price' => $product['sale_price'] ? $product['sale_price'] : $product['price'],
            'quantity' => $quantity,
            'attributes' => $attributes,
            'image' => get_product_primary_image($product_id)
        ];
    }
    
    return true;
}

/**
 * Update cart item
 */
function update_cart_item($cart_item_id, $quantity) {
    if (isset($_SESSION['cart'][$cart_item_id])) {
        if ($quantity > 0) {
            $_SESSION['cart'][$cart_item_id]['quantity'] = $quantity;
        } else {
            remove_from_cart($cart_item_id);
        }
        return true;
    }
    return false;
}

/**
 * Remove from cart
 */
function remove_from_cart($cart_item_id) {
    if (isset($_SESSION['cart'][$cart_item_id])) {
        unset($_SESSION['cart'][$cart_item_id]);
        return true;
    }
    return false;
}

/**
 * Get cart total
 */
function get_cart_total() {
    $total = 0;
    
    if (isset($_SESSION['cart'])) {
        foreach ($_SESSION['cart'] as $item) {
            $total += $item['price'] * $item['quantity'];
        }
    }
    
    return $total;
}

/**
 * Get cart item count
 */
function get_cart_item_count() {
    $count = 0;
    
    if (isset($_SESSION['cart'])) {
        foreach ($_SESSION['cart'] as $item) {
            $count += $item['quantity'];
        }
    }
    
    return $count;
}

/**
 * Get product primary image
 */
function get_product_primary_image($product_id) {
    global $conn;
    
    $sql = "SELECT image_url FROM product_images WHERE product_id = ? AND is_primary = 1 LIMIT 1";
    
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "i", $product_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    if ($row = mysqli_fetch_assoc($result)) {
        return $row['image_url'];
    }
    
    // Return default image if no primary image found
    return 'assets/images/default-product.jpg';
}

/**
 * Add to wishlist
 */
function add_to_wishlist($user_id, $product_id) {
    global $conn;
    
    $sql = "INSERT IGNORE INTO wishlist (user_id, product_id) VALUES (?, ?)";
    
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "ii", $user_id, $product_id);
    
    return mysqli_stmt_execute($stmt);
}

/**
 * Remove from wishlist
 */
function remove_from_wishlist($user_id, $product_id) {
    global $conn;
    
    $sql = "DELETE FROM wishlist WHERE user_id = ? AND product_id = ?";
    
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "ii", $user_id, $product_id);
    
    return mysqli_stmt_execute($stmt);
}

/**
 * Check if product is in wishlist
 */
function is_in_wishlist($user_id, $product_id) {
    global $conn;
    
    $sql = "SELECT 1 FROM wishlist WHERE user_id = ? AND product_id = ?";
    
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "ii", $user_id, $product_id);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_store_result($stmt);
    
    return mysqli_stmt_num_rows($stmt) > 0;
}

/**
 * Get user wishlist
 */
function get_user_wishlist($user_id) {
    global $conn;
    
    $sql = "SELECT p.*, 
            (SELECT image_url FROM product_images WHERE product_id = p.id AND is_primary = 1 LIMIT 1) as primary_image 
            FROM wishlist w 
            JOIN products p ON w.product_id = p.id 
            WHERE w.user_id = ? 
            ORDER BY w.created_at DESC";
    
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "i", $user_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    $products = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $products[] = $row;
    }
    
    return $products;
}

/**
 * Subscribe to newsletter
 */
function subscribe_to_newsletter($email) {
    global $conn;
    
    $sql = "INSERT IGNORE INTO newsletter_subscribers (email) VALUES (?)";
    
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "s", $email);
    
    return mysqli_stmt_execute($stmt);
}

/**
 * Send contact message
 */
function send_contact_message($name, $email, $subject, $message) {
    global $conn;
    
    $sql = "INSERT INTO contact_messages (name, email, subject, message) VALUES (?, ?, ?, ?)";
    
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "ssss", $name, $email, $subject, $message);
    
    return mysqli_stmt_execute($stmt);
}

/**
 * Create order
 */
function create_order($user_id, $total_amount, $shipping_address, $billing_address, $payment_method) {
    global $conn;
    
    $sql = "INSERT INTO orders (user_id, total_amount, shipping_address, billing_address, payment_method) 
            VALUES (?, ?, ?, ?, ?)";
    
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "idsss", $user_id, $total_amount, $shipping_address, $billing_address, $payment_method);
    
    if (mysqli_stmt_execute($stmt)) {
        return mysqli_insert_id($conn);
    }
    
    return false;
}

/**
 * Add order item
 */
function add_order_item($order_id, $product_id, $quantity, $price, $attributes = null) {
    global $conn;
    
    $sql = "INSERT INTO order_items (order_id, product_id, quantity, price, attributes) 
            VALUES (?, ?, ?, ?, ?)";
    
    $attrs = $attributes ? json_encode($attributes) : null;
    
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "iiids", $order_id, $product_id, $quantity, $price, $attrs);
    
    return mysqli_stmt_execute($stmt);
}

/**
 * Update order status
 */
function update_order_status($order_id, $status) {
    global $conn;
    
    $sql = "UPDATE orders SET order_status = ? WHERE id = ?";
    
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "si", $status, $order_id);
    
    return mysqli_stmt_execute($stmt);
}

/**
 * Update payment status
 */
function update_payment_status($order_id, $status) {
    global $conn;
    
    $sql = "UPDATE orders SET payment_status = ? WHERE id = ?";
    
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "si", $status, $order_id);
    
    return mysqli_stmt_execute($stmt);
}

/**
 * Get user orders
 */
function get_user_orders($user_id) {
    global $conn;
    
    $sql = "SELECT * FROM orders WHERE user_id = ? ORDER BY created_at DESC";
    
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "i", $user_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    $orders = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $orders[] = $row;
    }
    
    return $orders;
}

/**
 * Get order details
 */
function get_order_details($order_id) {
    global $conn;
    
    $sql = "SELECT o.*, u.first_name, u.last_name, u.email 
            FROM orders o 
            LEFT JOIN users u ON o.user_id = u.id 
            WHERE o.id = ?";
    
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "i", $order_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    if ($result && mysqli_num_rows($result) > 0) {
        return mysqli_fetch_assoc($result);
    }
    
    return null;
}

/**
 * Get order items
 */
function get_order_items($order_id) {
    global $conn;
    
    $sql = "SELECT oi.*, p.name, p.sku 
            FROM order_items oi 
            JOIN products p ON oi.product_id = p.id 
            WHERE oi.order_id = ?";
    
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "i", $order_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    $items = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $items[] = $row;
    }
    
    return $items;
}

/**
 * Save chat message
 */
function save_chat_message($user_id, $session_id, $message, $is_bot = false) {
    global $conn;
    
    $sql = "INSERT INTO chat_messages (user_id, session_id, message, is_bot) 
            VALUES (?, ?, ?, ?)";
    
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "issi", $user_id, $session_id, $message, $is_bot);
    
    return mysqli_stmt_execute($stmt);
}

/**
 * Get chat history
 */
function get_chat_history($session_id, $limit = 10) {
    global $conn;
    
    $sql = "SELECT * FROM chat_messages 
            WHERE session_id = ? 
            ORDER BY created_at DESC 
            LIMIT ?";
    
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "si", $session_id, $limit);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    $messages = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $messages[] = $row;
    }
    
    // Reverse to get chronological order
    return array_reverse($messages);
}
?>
