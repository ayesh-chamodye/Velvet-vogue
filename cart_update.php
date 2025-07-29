<?php
// Include functions
require_once 'includes/functions.php';

// Check if form was submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get cart item ID and quantity
    $cart_item_id = isset($_POST['cart_item_id']) ? $_POST['cart_item_id'] : '';
    $quantity = isset($_POST['quantity']) ? (int)$_POST['quantity'] : 1;
    
    // Validate quantity
    if ($quantity < 1) {
        $quantity = 1;
    }
    
    // Update cart item
    $updated = update_cart_item($cart_item_id, $quantity);
    
    if ($updated) {
        set_message('Cart updated successfully.', 'success');
    } else {
        set_message('Failed to update cart.', 'danger');
    }
}

// Redirect back to cart page
redirect('cart.php');
?>
