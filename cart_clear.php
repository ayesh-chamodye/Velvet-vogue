<?php
// Include functions
require_once 'includes/functions.php';

// Clear cart
$cleared = clear_cart();

if ($cleared) {
    set_message('Your cart has been cleared.', 'success');
} else {
    set_message('Failed to clear cart.', 'danger');
}

// Redirect back to cart page
redirect('cart.php');
?>
