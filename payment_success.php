<?php
// Include functions
require_once 'includes/functions.php';

// Get order ID from URL
$order_id = isset($_GET['order_id']) ? (int)$_GET['order_id'] : 0;

// Get order details
$order = get_order($order_id);

// Check if order exists and belongs to the current user if logged in
if (!$order || (is_logged_in() && $order['user_id'] != $_SESSION['user_id'])) {
    set_message('Invalid order.', 'danger');
    redirect('index.php');
    exit;
}

// Clear cart and order ID from session
clear_cart();
unset($_SESSION['order_id']);

// Set page title
$page_title = 'Payment Successful';
$meta_description = 'Payment successful at Velvet Vogue - Fashion E-commerce Store';
$meta_keywords = 'payment, success, order confirmation, fashion, clothing, Velvet Vogue';

// Include header
include_once 'includes/header.php';
?>

<!-- Breadcrumb -->
<nav aria-label="breadcrumb" class="bg-light py-3">
    <div class="container">
        <ol class="breadcrumb mb-0">
            <li class="breadcrumb-item"><a href="index.php">Home</a></li>
            <li class="breadcrumb-item active" aria-current="page">Payment Successful</li>
        </ol>
    </div>
</nav>

<!-- Payment Success Section -->
<section class="payment-success-page py-5">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-body p-5 text-center">
                        <div class="success-icon mb-4">
                            <i class="fas fa-check-circle fa-5x text-success"></i>
                        </div>
                        
                        <h1 class="h3 mb-3">Payment Successful!</h1>
                        <p class="lead mb-4">Thank you for your purchase. Your order has been confirmed.</p>
                        
                        <div class="order-details mb-4">
                            <h5>Order Details</h5>
                            <p><strong>Order Number:</strong> #<?php echo $order['id']; ?></p>
                            <p><strong>Date:</strong> <?php echo date('F j, Y', strtotime($order['created_at'])); ?></p>
                            <p><strong>Total:</strong> <?php echo format_price($order['total']); ?></p>
                            <p><strong>Payment Method:</strong> <?php echo ucfirst($order['payment_method']); ?></p>
                        </div>
                        
                        <p class="mb-4">
                            We've sent a confirmation email to <strong><?php echo $order['email']; ?></strong> with your order details.
                            You can also view your order status in your account dashboard.
                        </p>
                        
                        <div class="d-flex justify-content-center gap-3">
                            <a href="index.php" class="btn btn-primary">
                                Continue Shopping
                            </a>
                            <?php if (is_logged_in()): ?>
                                <a href="account_orders.php" class="btn btn-outline-secondary">
                                    View My Orders
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                
                <!-- Product Recommendations -->
                <div class="recommendations mt-5">
                    <h3 class="mb-4">You Might Also Like</h3>
                    <div class="row">
                        <?php
                        // Get random featured products
                        $featured_products = get_featured_products(4);
                        foreach ($featured_products as $product):
                        ?>
                            <div class="col-md-3 col-6 mb-4">
                                <div class="product-card">
                                    <div class="product-image">
                                        <img src="<?php echo !empty($product['image']) ? $product['image'] : 'assets/images/products/default.jpg'; ?>" alt="<?php echo $product['name']; ?>">
                                        <div class="product-actions">
                                            <a href="wishlist_add.php?product_id=<?php echo $product['id']; ?>" class="btn"><i class="fas fa-heart"></i></a>
                                            <a href="product.php?id=<?php echo $product['id']; ?>" class="btn"><i class="fas fa-eye"></i></a>
                                        </div>
                                        <?php if ($product['sale_price']): ?>
                                            <div class="product-badge badge-sale">Sale</div>
                                        <?php elseif (strtotime($product['created_at']) > strtotime('-7 days')): ?>
                                            <div class="product-badge badge-new">New</div>
                                        <?php endif; ?>
                                    </div>
                                    <div class="product-info">
                                        <h3 class="product-title">
                                            <a href="product.php?id=<?php echo $product['id']; ?>"><?php echo $product['name']; ?></a>
                                        </h3>
                                        <div class="product-price">
                                            <?php if ($product['sale_price']): ?>
                                                <span class="current-price"><?php echo format_price($product['sale_price']); ?></span>
                                                <span class="old-price"><?php echo format_price($product['price']); ?></span>
                                            <?php else: ?>
                                                <span class="current-price"><?php echo format_price($product['price']); ?></span>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<?php
// Include footer
include_once 'includes/footer.php';
?>
