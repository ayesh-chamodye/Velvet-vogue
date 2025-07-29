<?php
// Include functions
require_once 'includes/functions.php';

// Get cart item ID
$cart_item_id = isset($_GET['cart_item_id']) ? $_GET['cart_item_id'] : '';

// Remove item from cart
if (!empty($cart_item_id)) {
    $removed = remove_from_cart($cart_item_id);
    
    if ($removed) {
        set_message('Item removed from cart successfully.', 'success');
    } else {
        set_message('Failed to remove item from cart.', 'danger');
    }
}

// Redirect back to cart page
redirect('cart.php');
?>
