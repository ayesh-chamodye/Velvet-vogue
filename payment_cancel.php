<?php
// Include functions
require_once 'includes/functions.php';

// Get order ID from URL
$order_id = isset($_GET['order_id']) ? (int)$_GET['order_id'] : 0;

// Update order status to 'cancelled' if order exists
if ($order_id > 0) {
    update_order_status($order_id, 'cancelled');
}

// Clear order ID from session
unset($_SESSION['order_id']);

// Set page title
$page_title = 'Payment Cancelled';
$meta_description = 'Payment cancelled at Velvet Vogue - Fashion E-commerce Store';
$meta_keywords = 'payment, cancelled, fashion, clothing, Velvet Vogue';

// Include header
include_once 'includes/header.php';
?>

<!-- Breadcrumb -->
<nav aria-label="breadcrumb" class="bg-light py-3">
    <div class="container">
        <ol class="breadcrumb mb-0">
            <li class="breadcrumb-item"><a href="index.php">Home</a></li>
            <li class="breadcrumb-item active" aria-current="page">Payment Cancelled</li>
        </ol>
    </div>
</nav>

<!-- Payment Cancel Section -->
<section class="payment-cancel-page py-5">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-body p-5 text-center">
                        <div class="cancel-icon mb-4">
                            <i class="fas fa-times-circle fa-5x text-danger"></i>
                        </div>
                        
                        <h1 class="h3 mb-3">Payment Cancelled</h1>
                        <p class="lead mb-4">Your payment has been cancelled and no charges have been made.</p>
                        
                        <p class="mb-4">
                            Your order is still saved in your cart. You can try again or choose a different payment method.
                        </p>
                        
                        <div class="d-flex justify-content-center gap-3">
                            <a href="checkout.php" class="btn btn-primary">
                                Return to Checkout
                            </a>
                            <a href="cart.php" class="btn btn-outline-secondary">
                                View Cart
                            </a>
                        </div>
                    </div>
                </div>
                
                <div class="payment-help mt-4">
                    <div class="card">
                        <div class="card-body">
                            <h5>Need Help?</h5>
                            <p>If you're experiencing issues with payment, here are some things you can try:</p>
                            <ul>
                                <li>Make sure your payment details are entered correctly</li>
                                <li>Check if your card has sufficient funds</li>
                                <li>Try a different payment method</li>
                                <li>Contact your bank to ensure they're not blocking the transaction</li>
                            </ul>
                            <p>If you continue to experience issues, please <a href="contact.php">contact our support team</a> for assistance.</p>
                        </div>
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
