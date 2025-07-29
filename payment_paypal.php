<?php
// Include functions
require_once 'includes/functions.php';

// Check if order ID is set
if (!isset($_SESSION['order_id'])) {
    set_message('Invalid order. Please try again.', 'danger');
    redirect('checkout.php');
    exit;
}

// Get order ID
$order_id = $_SESSION['order_id'];

// Get order details
$order = get_order($order_id);

// Check if order exists
if (!$order) {
    set_message('Order not found.', 'danger');
    redirect('checkout.php');
    exit;
}

// PayPal configuration
$paypal_config = [
    'sandbox' => true, // Set to false for production
    'client_id' => 'YOUR_PAYPAL_CLIENT_ID', // Replace with your PayPal client ID
    'client_secret' => 'YOUR_PAYPAL_CLIENT_SECRET', // Replace with your PayPal client secret
    'currency' => 'USD',
    'return_url' => 'https://' . $_SERVER['HTTP_HOST'] . '/Velvet%20vogue/payment_success.php',
    'cancel_url' => 'https://' . $_SERVER['HTTP_HOST'] . '/Velvet%20vogue/payment_cancel.php'
];

// Set page title
$page_title = 'PayPal Payment';
$meta_description = 'Complete your payment with PayPal at Velvet Vogue - Fashion E-commerce Store';
$meta_keywords = 'payment, PayPal, fashion, clothing, Velvet Vogue';

// Include header
include_once 'includes/header.php';
?>

<!-- Breadcrumb -->
<nav aria-label="breadcrumb" class="bg-light py-3">
    <div class="container">
        <ol class="breadcrumb mb-0">
            <li class="breadcrumb-item"><a href="index.php">Home</a></li>
            <li class="breadcrumb-item"><a href="cart.php">Shopping Cart</a></li>
            <li class="breadcrumb-item"><a href="checkout.php">Checkout</a></li>
            <li class="breadcrumb-item active" aria-current="page">PayPal Payment</li>
        </ol>
    </div>
</nav>

<!-- PayPal Payment Section -->
<section class="payment-page py-5">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header bg-white text-center py-3">
                        <h1 class="h4 mb-0">PayPal Payment</h1>
                    </div>
                    <div class="card-body p-4 text-center">
                        <div class="mb-4">
                            <img src="assets/images/payment/paypal.png" alt="PayPal" height="60">
                        </div>
                        
                        <h5 class="mb-3">Order #<?php echo $order['id']; ?></h5>
                        <p class="mb-4">Total Amount: <strong><?php echo format_price($order['total']); ?></strong></p>
                        
                        <div class="paypal-button-container">
                            <div id="paypal-button-container"></div>
                            <div class="text-center mt-3">
                                <div class="spinner-border text-primary d-none" id="payment-loading" role="status">
                                    <span class="visually-hidden">Loading...</span>
                                </div>
                            </div>
                        </div>
                        
                        <p class="mt-4 mb-0 small text-muted">
                            You will be redirected to PayPal to complete your payment securely.
                            After payment is completed, you will be redirected back to our website.
                        </p>
                    </div>
                </div>
                
                <div class="text-center mt-4">
                    <a href="payment_cancel.php" class="btn btn-outline-secondary">
                        <i class="fas fa-arrow-left me-2"></i> Cancel and Return to Checkout
                    </a>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- PayPal JavaScript SDK -->
<script src="https://www.paypal.com/sdk/js?client-id=<?php echo $paypal_config['client_id']; ?>&currency=<?php echo $paypal_config['currency']; ?>"></script>

<script>
    // Render the PayPal button
    paypal.Buttons({
        // Set up the transaction
        createOrder: function(data, actions) {
            document.getElementById('payment-loading').classList.remove('d-none');
            
            return actions.order.create({
                purchase_units: [{
                    description: 'Order #<?php echo $order['id']; ?> from Velvet Vogue',
                    amount: {
                        currency_code: '<?php echo $paypal_config['currency']; ?>',
                        value: '<?php echo number_format($order['total'], 2, '.', ''); ?>'
                    }
                }]
            });
        },
        
        // Finalize the transaction
        onApprove: function(data, actions) {
            document.getElementById('payment-loading').classList.remove('d-none');
            
            return actions.order.capture().then(function(details) {
                // Show a success message
                const transaction = details.purchase_units[0].payments.captures[0];
                
                // Send the payment details to the server
                fetch('payment_process.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        order_id: '<?php echo $order['id']; ?>',
                        payment_id: transaction.id,
                        payment_status: transaction.status,
                        payment_amount: transaction.amount.value,
                        payment_currency: transaction.amount.currency_code,
                        payment_method: 'paypal',
                        payment_details: JSON.stringify(details)
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        window.location.href = '<?php echo $paypal_config['return_url']; ?>?order_id=<?php echo $order['id']; ?>';
                    } else {
                        alert('Payment verification failed. Please contact customer support.');
                        window.location.href = '<?php echo $paypal_config['cancel_url']; ?>?order_id=<?php echo $order['id']; ?>';
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('An error occurred during payment processing. Please contact customer support.');
                    window.location.href = '<?php echo $paypal_config['cancel_url']; ?>?order_id=<?php echo $order['id']; ?>';
                });
            });
        },
        
        // Handle errors
        onError: function(err) {
            console.error('PayPal Error:', err);
            alert('An error occurred with PayPal. Please try again or choose a different payment method.');
            document.getElementById('payment-loading').classList.add('d-none');
        },
        
        // Cancel transaction
        onCancel: function(data) {
            window.location.href = '<?php echo $paypal_config['cancel_url']; ?>?order_id=<?php echo $order['id']; ?>';
        }
    }).render('#paypal-button-container');
</script>

<?php
// Include footer
include_once 'includes/footer.php';
?>
