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

// Stripe configuration
$stripe_config = [
    'publishable_key' => 'YOUR_STRIPE_PUBLISHABLE_KEY', // Replace with your Stripe publishable key
    'secret_key' => 'YOUR_STRIPE_SECRET_KEY', // Replace with your Stripe secret key
    'currency' => 'usd',
    'success_url' => 'https://' . $_SERVER['HTTP_HOST'] . '/Velvet%20vogue/payment_success.php?order_id=' . $order_id,
    'cancel_url' => 'https://' . $_SERVER['HTTP_HOST'] . '/Velvet%20vogue/payment_cancel.php?order_id=' . $order_id
];

// Set page title
$page_title = 'Stripe Payment';
$meta_description = 'Complete your payment with Stripe at Velvet Vogue - Fashion E-commerce Store';
$meta_keywords = 'payment, Stripe, credit card, fashion, clothing, Velvet Vogue';

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
            <li class="breadcrumb-item active" aria-current="page">Stripe Payment</li>
        </ol>
    </div>
</nav>

<!-- Stripe Payment Section -->
<section class="payment-page py-5">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header bg-white text-center py-3">
                        <h1 class="h4 mb-0">Credit Card Payment</h1>
                    </div>
                    <div class="card-body p-4">
                        <div class="mb-4 text-center">
                            <img src="assets/images/payment/stripe.png" alt="Stripe" height="40">
                            <div class="payment-cards mt-2">
                                <i class="fab fa-cc-visa fa-2x me-2"></i>
                                <i class="fab fa-cc-mastercard fa-2x me-2"></i>
                                <i class="fab fa-cc-amex fa-2x me-2"></i>
                                <i class="fab fa-cc-discover fa-2x"></i>
                            </div>
                        </div>
                        
                        <div class="order-summary mb-4">
                            <h5>Order Summary</h5>
                            <div class="d-flex justify-content-between">
                                <span>Order #<?php echo $order['id']; ?></span>
                                <span><strong><?php echo format_price($order['total']); ?></strong></span>
                            </div>
                        </div>
                        
                        <form id="payment-form" class="stripe-form">
                            <div class="mb-3">
                                <label for="card-element" class="form-label">Credit or debit card</label>
                                <div id="card-element" class="form-control">
                                    <!-- Stripe Element will be inserted here -->
                                </div>
                                <div id="card-errors" role="alert" class="text-danger mt-2 small"></div>
                            </div>
                            
                            <div class="d-grid">
                                <button type="submit" id="submit-button" class="btn btn-primary">
                                    <span id="button-text">Pay <?php echo format_price($order['total']); ?></span>
                                    <span id="spinner" class="spinner-border spinner-border-sm ms-2 d-none" role="status" aria-hidden="true"></span>
                                </button>
                            </div>
                        </form>
                        
                        <p class="mt-4 mb-0 small text-muted text-center">
                            Your payment information is processed securely. We do not store credit card details.
                        </p>
                    </div>
                </div>
                
                <div class="text-center mt-4">
                    <a href="payment_cancel.php?order_id=<?php echo $order_id; ?>" class="btn btn-outline-secondary">
                        <i class="fas fa-arrow-left me-2"></i> Cancel and Return to Checkout
                    </a>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Stripe JavaScript SDK -->
<script src="https://js.stripe.com/v3/"></script>

<script>
    // Create a Stripe client
    const stripe = Stripe('<?php echo $stripe_config['publishable_key']; ?>');
    
    // Create an instance of Elements
    const elements = stripe.elements();
    
    // Custom styling
    const style = {
        base: {
            color: '#32325d',
            fontFamily: '"Helvetica Neue", Helvetica, sans-serif',
            fontSmoothing: 'antialiased',
            fontSize: '16px',
            '::placeholder': {
                color: '#aab7c4'
            }
        },
        invalid: {
            color: '#fa755a',
            iconColor: '#fa755a'
        }
    };
    
    // Create an instance of the card Element
    const card = elements.create('card', {style: style});
    
    // Add an instance of the card Element into the `card-element` div
    card.mount('#card-element');
    
    // Handle real-time validation errors from the card Element
    card.addEventListener('change', function(event) {
        const displayError = document.getElementById('card-errors');
        if (event.error) {
            displayError.textContent = event.error.message;
        } else {
            displayError.textContent = '';
        }
    });
    
    // Handle form submission
    const form = document.getElementById('payment-form');
    form.addEventListener('submit', function(event) {
        event.preventDefault();
        
        // Disable the submit button to prevent repeated clicks
        document.getElementById('submit-button').disabled = true;
        document.getElementById('spinner').classList.remove('d-none');
        document.getElementById('button-text').textContent = 'Processing...';
        
        // Create a token
        stripe.createToken(card).then(function(result) {
            if (result.error) {
                // Inform the user if there was an error
                const errorElement = document.getElementById('card-errors');
                errorElement.textContent = result.error.message;
                
                // Enable the submit button
                document.getElementById('submit-button').disabled = false;
                document.getElementById('spinner').classList.add('d-none');
                document.getElementById('button-text').textContent = 'Pay <?php echo format_price($order['total']); ?>';
            } else {
                // Send the token to your server
                stripeTokenHandler(result.token);
            }
        });
    });
    
    // Submit the form with the token ID
    function stripeTokenHandler(token) {
        // Insert the token ID into the form so it gets submitted to the server
        const form = document.getElementById('payment-form');
        const hiddenInput = document.createElement('input');
        hiddenInput.setAttribute('type', 'hidden');
        hiddenInput.setAttribute('name', 'stripeToken');
        hiddenInput.setAttribute('value', token.id);
        form.appendChild(hiddenInput);
        
        // Submit the form to our backend
        fetch('payment_process.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                order_id: '<?php echo $order['id']; ?>',
                payment_method: 'stripe',
                stripe_token: token.id,
                amount: <?php echo number_format($order['total'], 2, '.', ''); ?>,
                currency: '<?php echo $stripe_config['currency']; ?>'
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                window.location.href = '<?php echo $stripe_config['success_url']; ?>';
            } else {
                document.getElementById('card-errors').textContent = data.error || 'Payment failed. Please try again.';
                document.getElementById('submit-button').disabled = false;
                document.getElementById('spinner').classList.add('d-none');
                document.getElementById('button-text').textContent = 'Pay <?php echo format_price($order['total']); ?>';
            }
        })
        .catch(error => {
            console.error('Error:', error);
            document.getElementById('card-errors').textContent = 'An error occurred during payment processing. Please try again.';
            document.getElementById('submit-button').disabled = false;
            document.getElementById('spinner').classList.add('d-none');
            document.getElementById('button-text').textContent = 'Pay <?php echo format_price($order['total']); ?>';
        });
    }
</script>

<?php
// Include footer
include_once 'includes/footer.php';
?>
