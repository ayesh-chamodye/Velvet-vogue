<?php
// search.php - Handles search queries and redirects to the appropriate product or search results page
require_once 'config/database.php';
require_once 'includes/functions.php';

$q = isset($_GET['q']) ? trim($_GET['q']) : '';
if ($q === '') {
    // If no query, redirect to home
    header('Location: index.php');
    exit;
}

// Search for a product by name (exact or partial match)
$sql = "SELECT id FROM products WHERE status = 'active' AND name LIKE ? LIMIT 1";
$stmt = mysqli_prepare($conn, $sql);
$like = '%' . $q . '%';
mysqli_stmt_bind_param($stmt, 's', $like);
mysqli_stmt_execute($stmt);
mysqli_stmt_bind_result($stmt, $product_id);

if (mysqli_stmt_fetch($stmt)) {
    // If a product is found, redirect to its page
    header('Location: product.php?id=' . $product_id);
    exit;
}
mysqli_stmt_close($stmt);

// If not found, redirect to a search results page (could be products.php with a search param)
header('Location: index.php?search=' . urlencode($q));
exit;
