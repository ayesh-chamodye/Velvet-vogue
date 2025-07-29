<?php
// Include functions
require_once 'includes/functions.php';

// Check if user is logged in
if (!is_logged_in()) {
    set_message('Please login to manage your wishlist.', 'warning');
    redirect('login.php');
    exit;
}

// Get wishlist ID
$wishlist_id = isset($_GET['wishlist_id']) ? (int)$_GET['wishlist_id'] : 0;

// Remove from wishlist
if ($wishlist_id > 0) {
    $removed = remove_from_wishlist($wishlist_id);
    
    if ($removed) {
        set_message('Product removed from your wishlist successfully.', 'success');
    } else {
        set_message('Failed to remove product from wishlist.', 'danger');
    }
}

// Redirect back to wishlist page
redirect('wishlist.php');
?>
