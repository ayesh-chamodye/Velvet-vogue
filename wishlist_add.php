<?php
// Include functions
require_once 'includes/functions.php';

// Check if user is logged in
if (!is_logged_in()) {
    set_message('Please login to add products to your wishlist.', 'warning');
    redirect('login.php');
    exit;
}

// Get product ID
$product_id = isset($_GET['product_id']) ? (int)$_GET['product_id'] : 0;

// Add to wishlist
if ($product_id > 0) {
    $added = add_to_wishlist($product_id);
    
    if ($added) {
        set_message('Product added to your wishlist successfully.', 'success');
    } else {
        set_message('Failed to add product to wishlist or product already in wishlist.', 'danger');
    }
}

// Redirect back to previous page or product page
$redirect_url = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : 'index.php';
redirect($redirect_url);
?>
