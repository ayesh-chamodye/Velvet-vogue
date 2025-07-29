<?php
// Include functions
require_once 'includes/functions.php';

// Check if cart is empty
$cart_items = get_cart_items();
if (empty($cart_items)) {
    set_message('Your cart is empty. Please add products to your cart before checkout.', 'warning');
    redirect('cart.php');
    exit;
}

// Calculate cart total
$cart_total = calculate_cart_total();

// Process checkout form
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get form data
    $first_name = isset($_POST['first_name']) ? clean_input($_POST['first_name']) : '';
    $last_name = isset($_POST['last_name']) ? clean_input($_POST['last_name']) : '';
    $email = isset($_POST['email']) ? clean_input($_POST['email']) : '';
    $phone = isset($_POST['phone']) ? clean_input($_POST['phone']) : '';
    $address = isset($_POST['address']) ? clean_input($_POST['address']) : '';
    $city = isset($_POST['city']) ? clean_input($_POST['city']) : '';
    $state = isset($_POST['state']) ? clean_input($_POST['state']) : '';
    $zip = isset($_POST['zip']) ? clean_input($_POST['zip']) : '';
    $country = isset($_POST['country']) ? clean_input($_POST['country']) : '';
    $payment_method = isset($_POST['payment_method']) ? clean_input($_POST['payment_method']) : '';
    
    // Validate form data
    $errors = [];
    
    if (empty($first_name)) {
        $errors[] = 'First name is required.';
    }
    
    if (empty($last_name)) {
        $errors[] = 'Last name is required.';
    }
    
    if (empty($email)) {
        $errors[] = 'Email is required.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Invalid email format.';
    }
    
    if (empty($phone)) {
        $errors[] = 'Phone is required.';
    }
    
    if (empty($address)) {
        $errors[] = 'Address is required.';
    }
    
    if (empty($city)) {
        $errors[] = 'City is required.';
    }
    
    if (empty($state)) {
        $errors[] = 'State is required.';
    }
    
    if (empty($zip)) {
        $errors[] = 'ZIP code is required.';
    }
    
    if (empty($country)) {
        $errors[] = 'Country is required.';
    }
    
    if (empty($payment_method)) {
        $errors[] = 'Payment method is required.';
    }
    
    // If no errors, process the order
    if (empty($errors)) {
        // Create order
        $order_data = [
            'user_id' => is_logged_in() ? $_SESSION['user_id'] : null,
            'first_name' => $first_name,
            'last_name' => $last_name,
            'email' => $email,
            'phone' => $phone,
            'address' => $address,
            'city' => $city,
            'state' => $state,
            'zip' => $zip,
            'country' => $country,
            'payment_method' => $payment_method,
            'subtotal' => $cart_total['subtotal'],
            'discount' => $cart_total['discount'],
            'shipping' => $cart_total['shipping'],
            'tax' => $cart_total['tax'],
            'total' => $cart_total['total'],
            'items' => $cart_items
        ];
        
        $order_id = create_order($order_data);
        
        if ($order_id) {
            // Process payment based on payment method
            $payment_success = false;
            
            switch ($payment_method) {
                case 'paypal':
                    // Redirect to PayPal
                    $_SESSION['order_id'] = $order_id;
                    redirect('payment_paypal.php');
                    exit;
                    
                case 'stripe':
                    // Redirect to Stripe
                    $_SESSION['order_id'] = $order_id;
                    redirect('payment_stripe.php');
                    exit;
                    
                case 'cod':
                    // Cash on delivery - no payment processing needed
                    $payment_success = true;
                    break;
                    
                default:
                    $errors[] = 'Invalid payment method.';
                    break;
            }
            
            if ($payment_success) {
                // Update order status
                update_order_status($order_id, 'pending');
                
                // Clear cart
                clear_cart();
                
                // Redirect to order confirmation
                redirect('order_confirmation.php?order_id=' . $order_id);
                exit;
            }
        } else {
            $errors[] = 'Failed to create order. Please try again.';
        }
    }
}

// Set page title
$page_title = 'Checkout';
$meta_description = 'Complete your purchase at Velvet Vogue - Fashion E-commerce Store';
$meta_keywords = 'checkout, payment, fashion, clothing, Velvet Vogue';

// Include header
include_once 'includes/header.php';
?>

<!-- Breadcrumb -->
<nav aria-label="breadcrumb" class="bg-light py-3">
    <div class="container">
        <ol class="breadcrumb mb-0">
            <li class="breadcrumb-item"><a href="index.php">Home</a></li>
            <li class="breadcrumb-item"><a href="cart.php">Shopping Cart</a></li>
            <li class="breadcrumb-item active" aria-current="page">Checkout</li>
        </ol>
    </div>
</nav>

<!-- Checkout Section -->
<section class="checkout-page py-5">
    <div class="container">
        <h1 class="mb-4">Checkout</h1>
        
        <?php if (isset($errors) && !empty($errors)): ?>
            <div class="alert alert-danger">
                <ul class="mb-0">
                    <?php foreach ($errors as $error): ?>
                        <li><?php echo $error; ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>
        
        <div class="row">
            <div class="col-lg-8">
                <div class="checkout-form card mb-4">
                    <div class="card-body">
                        <form action="checkout.php" method="POST" id="checkout-form">
                            <h3 class="mb-3">Billing Details</h3>
                            
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="first_name" class="form-label">First Name *</label>
                                    <input type="text" class="form-control" id="first_name" name="first_name" value="<?php echo isset($first_name) ? $first_name : ''; ?>" required>
                                </div>
                                
                                <div class="col-md-6 mb-3">
                                    <label for="last_name" class="form-label">Last Name *</label>
                                    <input type="text" class="form-control" id="last_name" name="last_name" value="<?php echo isset($last_name) ? $last_name : ''; ?>" required>
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <label for="email" class="form-label">Email *</label>
                                <input type="email" class="form-control" id="email" name="email" value="<?php echo isset($email) ? $email : ''; ?>" required>
                            </div>
                            
                            <div class="mb-3">
                                <label for="phone" class="form-label">Phone *</label>
                                <input type="tel" class="form-control" id="phone" name="phone" value="<?php echo isset($phone) ? $phone : ''; ?>" required>
                            </div>
                            
                            <div class="mb-3">
                                <label for="address" class="form-label">Address *</label>
                                <input type="text" class="form-control" id="address" name="address" value="<?php echo isset($address) ? $address : ''; ?>" required>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="city" class="form-label">City *</label>
                                    <input type="text" class="form-control" id="city" name="city" value="<?php echo isset($city) ? $city : ''; ?>" required>
                                </div>
                                
                                <div class="col-md-6 mb-3">
                                    <label for="state" class="form-label">State/Province *</label>
                                    <input type="text" class="form-control" id="state" name="state" value="<?php echo isset($state) ? $state : ''; ?>" required>
                                </div>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="zip" class="form-label">ZIP/Postal Code *</label>
                                    <input type="text" class="form-control" id="zip" name="zip" value="<?php echo isset($zip) ? $zip : ''; ?>" required>
                                </div>
                                
                                <div class="col-md-6 mb-3">
                                    <label for="country" class="form-label">Country *</label>
                                    <select class="form-select" id="country" name="country" required>
                                        <option value="">Select Country</option>
                                        <option value="US" <?php echo (isset($country) && $country === 'US') ? 'selected' : ''; ?>>United States</option>
                                        <option value="CA" <?php echo (isset($country) && $country === 'CA') ? 'selected' : ''; ?>>Canada</option>
                                        <option value="UK" <?php echo (isset($country) && $country === 'UK') ? 'selected' : ''; ?>>United Kingdom</option>
                                        <option value="AU" <?php echo (isset($country) && $country === 'AU') ? 'selected' : ''; ?>>Australia</option>
                                        <option value="IN" <?php echo (isset($country) && $country === 'IN') ? 'selected' : ''; ?>>India</option>
                                        <!-- Add more countries as needed -->
                                    </select>
                                </div>
                            </div>
                            
                            <div class="mb-3 form-check">
                                <input type="checkbox" class="form-check-input" id="shipping_same" name="shipping_same" checked>
                                <label class="form-check-label" for="shipping_same">Shipping address same as billing</label>
                            </div>
                            
                            <div id="shipping-address" class="d-none">
                                <!-- Shipping address fields (hidden by default) -->
                                <h3 class="mb-3 mt-4">Shipping Address</h3>
                                
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="shipping_first_name" class="form-label">First Name</label>
                                        <input type="text" class="form-control" id="shipping_first_name" name="shipping_first_name">
                                    </div>
                                    
                                    <div class="col-md-6 mb-3">
                                        <label for="shipping_last_name" class="form-label">Last Name</label>
                                        <input type="text" class="form-control" id="shipping_last_name" name="shipping_last_name">
                                    </div>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="shipping_address" class="form-label">Address</label>
                                    <input type="text" class="form-control" id="shipping_address" name="shipping_address">
                                </div>
                                
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="shipping_city" class="form-label">City</label>
                                        <input type="text" class="form-control" id="shipping_city" name="shipping_city">
                                    </div>
                                    
                                    <div class="col-md-6 mb-3">
                                        <label for="shipping_state" class="form-label">State/Province</label>
                                        <input type="text" class="form-control" id="shipping_state" name="shipping_state">
                                    </div>
                                </div>
                                
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="shipping_zip" class="form-label">ZIP/Postal Code</label>
                                        <input type="text" class="form-control" id="shipping_zip" name="shipping_zip">
                                    </div>
                                    
                                    <div class="col-md-6 mb-3">
                                        <label for="shipping_country" class="form-label">Country</label>
                                        <select class="form-select" id="shipping_country" name="shipping_country">
                                            <option value="">Select Country</option>
                                            <option value="US">United States</option>
                                            <option value="CA">Canada</option>
                                            <option value="UK">United Kingdom</option>
                                            <option value="AU">Australia</option>
                                            <option value="IN">India</option>
                                            <!-- Add more countries as needed -->
                                        </select>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <label for="order_notes" class="form-label">Order Notes (Optional)</label>
                                <textarea class="form-control" id="order_notes" name="order_notes" rows="3"></textarea>
                            </div>
                            
                            <h3 class="mb-3 mt-4">Payment Method</h3>
                            
                            <div class="payment-methods mb-4">
                                <div class="form-check mb-3">
                                    <input class="form-check-input" type="radio" name="payment_method" id="payment_paypal" value="paypal" <?php echo (isset($payment_method) && $payment_method === 'paypal') ? 'checked' : ''; ?>>
                                    <label class="form-check-label" for="payment_paypal">
                                        <img src="assets/images/payment/paypal.png" alt="PayPal" height="30">
                                        <span class="ms-2">PayPal</span>
                                    </label>
                                </div>
                                
                                <div class="form-check mb-3">
                                    <input class="form-check-input" type="radio" name="payment_method" id="payment_stripe" value="stripe" <?php echo (isset($payment_method) && $payment_method === 'stripe') ? 'checked' : ''; ?>>
                                    <label class="form-check-label" for="payment_stripe">
                                        <img src="assets/images/payment/stripe.png" alt="Stripe" height="30">
                                        <span class="ms-2">Credit Card (via Stripe)</span>
                                    </label>
                                </div>
                                
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="payment_method" id="payment_cod" value="cod" <?php echo (isset($payment_method) && $payment_method === 'cod') ? 'checked' : ''; ?>>
                                    <label class="form-check-label" for="payment_cod">
                                        <i class="fas fa-money-bill-wave fa-lg"></i>
                                        <span class="ms-2">Cash on Delivery</span>
                                    </label>
                                </div>
                            </div>
                            
                            <div class="form-check mb-4">
                                <input class="form-check-input" type="checkbox" id="terms_agree" name="terms_agree" required>
                                <label class="form-check-label" for="terms_agree">
                                    I have read and agree to the <a href="terms.php" class="text-decoration-none">Terms and Conditions</a> and <a href="privacy.php" class="text-decoration-none">Privacy Policy</a>
                                </label>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-4">
                <div class="order-summary card mb-4">
                    <div class="card-header">
                        <h4 class="mb-0">Order Summary</h4>
                    </div>
                    <div class="card-body">
                        <div class="order-products mb-3">
                            <?php foreach ($cart_items as $item): ?>
                                <div class="d-flex justify-content-between mb-2">
                                    <div>
                                        <span><?php echo $item['name']; ?></span>
                                        <small class="d-block text-muted">
                                            <?php echo $item['quantity']; ?> x <?php echo format_price($item['price']); ?>
                                            <?php if (!empty($item['attributes'])): ?>
                                                <br>
                                                <?php foreach ($item['attributes'] as $attr_name => $attr_value): ?>
                                                    <?php echo ucfirst($attr_name); ?>: <?php echo $attr_value; ?><br>
                                                <?php endforeach; ?>
                                            <?php endif; ?>
                                        </small>
                                    </div>
                                    <span><?php echo format_price($item['price'] * $item['quantity']); ?></span>
                                </div>
                            <?php endforeach; ?>
                        </div>
                        
                        <div class="order-totals">
                            <div class="d-flex justify-content-between mb-2">
                                <span>Subtotal:</span>
                                <span><?php echo format_price($cart_total['subtotal']); ?></span>
                            </div>
                            
                            <?php if ($cart_total['discount'] > 0): ?>
                                <div class="d-flex justify-content-between mb-2 text-success">
                                    <span>Discount:</span>
                                    <span>-<?php echo format_price($cart_total['discount']); ?></span>
                                </div>
                            <?php endif; ?>
                            
                            <div class="d-flex justify-content-between mb-2">
                                <span>Shipping:</span>
                                <span><?php echo $cart_total['shipping'] > 0 ? format_price($cart_total['shipping']) : 'Free'; ?></span>
                            </div>
                            
                            <?php if ($cart_total['tax'] > 0): ?>
                                <div class="d-flex justify-content-between mb-2">
                                    <span>Tax:</span>
                                    <span><?php echo format_price($cart_total['tax']); ?></span>
                                </div>
                            <?php endif; ?>
                            
                            <hr>
                            
                            <div class="d-flex justify-content-between mb-3">
                                <strong>Total:</strong>
                                <strong class="order-total-price"><?php echo format_price($cart_total['total']); ?></strong>
                            </div>
                        </div>
                        
                        <div class="d-grid">
                            <button type="submit" form="checkout-form" class="btn btn-primary btn-lg">
                                Place Order <i class="fas fa-arrow-right ms-2"></i>
                            </button>
                        </div>
                    </div>
                </div>
                
                <div class="secure-checkout card">
                    <div class="card-body">
                        <div class="text-center mb-3">
                            <i class="fas fa-lock fa-2x text-success"></i>
                            <h5 class="mt-2">Secure Checkout</h5>
                        </div>
                        <p class="mb-0 small">Your payment information is processed securely. We do not store credit card details nor have access to your credit card information.</p>
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
