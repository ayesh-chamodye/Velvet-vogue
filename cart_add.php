<?php
// Include functions
require_once 'includes/functions.php';

// Check if form was submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get product ID and quantity
    $product_id = isset($_POST['product_id']) ? (int)$_POST['product_id'] : 0;
    $quantity = isset($_POST['quantity']) ? (int)$_POST['quantity'] : 1;
    
    // Validate quantity
    if ($quantity < 1) {
        $quantity = 1;
    }
    
    // Get product attributes if any
    $attributes = isset($_POST['attributes']) ? $_POST['attributes'] : [];
    
    // Get product information
    $product = get_product($product_id);
    
    // Check if product exists and is active
    if ($product && $product['status'] === 'active') {
        // Check stock availability
        if ($product['stock'] >= $quantity) {
            // Add to cart
            $added = add_to_cart($product_id, $quantity, $attributes);
            
            if ($added) {
                set_message('Product added to your cart successfully.', 'success');
            } else {
                set_message('Failed to add product to cart.', 'danger');
            }
        } else {
            set_message('Sorry, we don\'t have enough stock for this product.', 'warning');
        }
    } else {
        set_message('Product not found or unavailable.', 'danger');
    }
}

// Redirect back to previous page or product page
$redirect_url = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : 'index.php';
redirect($redirect_url);
?>
