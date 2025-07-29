<?php
// Include functions
require_once 'includes/functions.php';

// Check if user is logged in
if (!is_logged_in()) {
    set_message('Please login to view your wishlist.', 'warning');
    redirect('login.php');
    exit;
}

// Get wishlist items
$wishlist_items = get_wishlist_items();

// Set page title
$page_title = 'My Wishlist';
$meta_description = 'View your saved items at Velvet Vogue - Fashion E-commerce Store';
$meta_keywords = 'fashion, clothing, wishlist, favorites, Velvet Vogue';

// Include header
include_once 'includes/header.php';
?>

<!-- Breadcrumb -->
<nav aria-label="breadcrumb" class="bg-light py-3">
    <div class="container">
        <ol class="breadcrumb mb-0">
            <li class="breadcrumb-item"><a href="index.php">Home</a></li>
            <li class="breadcrumb-item active" aria-current="page">My Wishlist</li>
        </ol>
    </div>
</nav>

<!-- Wishlist -->
<section class="wishlist-page py-5">
    <div class="container">
        <h1 class="mb-4">My Wishlist</h1>
        
        <?php if (empty($wishlist_items)): ?>
            <div class="empty-wishlist text-center py-5">
                <i class="fas fa-heart fa-4x mb-4 text-muted"></i>
                <h3>Your wishlist is empty</h3>
                <p class="mb-4">You haven't added any products to your wishlist yet.</p>
                <a href="index.php" class="btn btn-primary">Continue Shopping</a>
            </div>
        <?php else: ?>
            <div class="row">
                <?php foreach ($wishlist_items as $item): ?>
                    <div class="col-md-3 col-6 mb-4">
                        <div class="product-card">
                            <div class="product-image">
                                <img src="<?php echo !empty($item['image']) ? $item['image'] : 'assets/images/products/default.jpg'; ?>" alt="<?php echo $item['name']; ?>">
                                <div class="product-actions">
                                    <a href="wishlist_remove.php?wishlist_id=<?php echo $item['wishlist_id']; ?>" class="btn"><i class="fas fa-trash"></i></a>
                                    <a href="product.php?id=<?php echo $item['product_id']; ?>" class="btn"><i class="fas fa-eye"></i></a>
                                </div>
                                <?php if ($item['sale_price']): ?>
                                    <div class="product-badge badge-sale">Sale</div>
                                <?php elseif (strtotime($item['created_at']) > strtotime('-7 days')): ?>
                                    <div class="product-badge badge-new">New</div>
                                <?php endif; ?>
                            </div>
                            <div class="product-info">
                                <h3 class="product-title">
                                    <a href="product.php?id=<?php echo $item['product_id']; ?>"><?php echo $item['name']; ?></a>
                                </h3>
                                <div class="product-price">
                                    <?php if ($item['sale_price']): ?>
                                        <span class="current-price"><?php echo format_price($item['sale_price']); ?></span>
                                        <span class="old-price"><?php echo format_price($item['price']); ?></span>
                                    <?php else: ?>
                                        <span class="current-price"><?php echo format_price($item['price']); ?></span>
                                    <?php endif; ?>
                                </div>
                                <div class="product-actions-bottom d-flex">
                                    <form action="cart_add.php" method="POST" class="me-2">
                                        <input type="hidden" name="product_id" value="<?php echo $item['product_id']; ?>">
                                        <input type="hidden" name="quantity" value="1">
                                        <button type="submit" class="btn btn-primary btn-sm">Add to Cart</button>
                                    </form>
                                    <a href="wishlist_remove.php?wishlist_id=<?php echo $item['wishlist_id']; ?>" class="btn btn-outline-danger btn-sm">Remove</a>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</section>

<?php
// Include footer
include_once 'includes/footer.php';
?>
