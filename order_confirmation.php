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

// Get order items
$order_items = get_order_items($order_id);

// Set page title
$page_title = 'Order Confirmation';
$meta_description = 'Order confirmation at Velvet Vogue - Fashion E-commerce Store';
$meta_keywords = 'order, confirmation, fashion, clothing, Velvet Vogue';

// Include header
include_once 'includes/header.php';
?>

<!-- Breadcrumb -->
<nav aria-label="breadcrumb" class="bg-light py-3">
    <div class="container">
        <ol class="breadcrumb mb-0">
            <li class="breadcrumb-item"><a href="index.php">Home</a></li>
            <li class="breadcrumb-item active" aria-current="page">Order Confirmation</li>
        </ol>
    </div>
</nav>

<!-- Order Confirmation Section -->
<section class="order-confirmation-page py-5">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-10">
                <div class="card mb-4">
                    <div class="card-body p-4">
                        <div class="text-center mb-4">
                            <i class="fas fa-check-circle fa-4x text-success mb-3"></i>
                            <h1 class="h3">Thank You for Your Order!</h1>
                            <p class="lead">Your order has been placed successfully.</p>
                        </div>
                        
                        <div class="order-confirmation-details">
                            <div class="row mb-4">
                                <div class="col-md-6 mb-3 mb-md-0">
                                    <h5>Order Information</h5>
                                    <p><strong>Order Number:</strong> #<?php echo $order['id']; ?></p>
                                    <p><strong>Date:</strong> <?php echo date('F j, Y', strtotime($order['created_at'])); ?></p>
                                    <p><strong>Payment Method:</strong> <?php echo ucfirst($order['payment_method']); ?></p>
                                    <p><strong>Order Status:</strong> <span class="badge bg-<?php echo get_status_color($order['status']); ?>"><?php echo ucfirst($order['status']); ?></span></p>
                                </div>
                                
                                <div class="col-md-6">
                                    <h5>Customer Information</h5>
                                    <p><strong>Name:</strong> <?php echo $order['first_name'] . ' ' . $order['last_name']; ?></p>
                                    <p><strong>Email:</strong> <?php echo $order['email']; ?></p>
                                    <p><strong>Phone:</strong> <?php echo $order['phone']; ?></p>
                                </div>
                            </div>
                            
                            <div class="row mb-4">
                                <div class="col-md-6 mb-3 mb-md-0">
                                    <h5>Billing Address</h5>
                                    <address>
                                        <?php echo $order['first_name'] . ' ' . $order['last_name']; ?><br>
                                        <?php echo $order['address']; ?><br>
                                        <?php echo $order['city'] . ', ' . $order['state'] . ' ' . $order['zip']; ?><br>
                                        <?php echo $order['country']; ?>
                                    </address>
                                </div>
                                
                                <div class="col-md-6">
                                    <h5>Shipping Address</h5>
                                    <address>
                                        <?php 
                                        if (!empty($order['shipping_address'])) {
                                            echo $order['shipping_first_name'] . ' ' . $order['shipping_last_name'] . '<br>';
                                            echo $order['shipping_address'] . '<br>';
                                            echo $order['shipping_city'] . ', ' . $order['shipping_state'] . ' ' . $order['shipping_zip'] . '<br>';
                                            echo $order['shipping_country'];
                                        } else {
                                            echo $order['first_name'] . ' ' . $order['last_name'] . '<br>';
                                            echo $order['address'] . '<br>';
                                            echo $order['city'] . ', ' . $order['state'] . ' ' . $order['zip'] . '<br>';
                                            echo $order['country'];
                                        }
                                        ?>
                                    </address>
                                </div>
                            </div>
                            
                            <h5>Order Items</h5>
                            <div class="table-responsive mb-4">
                                <table class="table table-bordered">
                                    <thead>
                                        <tr>
                                            <th>Product</th>
                                            <th>Price</th>
                                            <th>Quantity</th>
                                            <th>Total</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($order_items as $item): ?>
                                            <tr>
                                                <td>
                                                    <div class="d-flex align-items-center">
                                                        <img src="<?php echo !empty($item['image']) ? $item['image'] : 'assets/images/products/default.jpg'; ?>" alt="<?php echo $item['name']; ?>" class="order-item-image me-3">
                                                        <div>
                                                            <h6 class="mb-0"><?php echo $item['name']; ?></h6>
                                                            <?php if (!empty($item['attributes'])): ?>
                                                                <small class="text-muted">
                                                                    <?php 
                                                                    $attributes = json_decode($item['attributes'], true);
                                                                    if ($attributes) {
                                                                        foreach ($attributes as $attr_name => $attr_value) {
                                                                            echo ucfirst($attr_name) . ': ' . $attr_value . '<br>';
                                                                        }
                                                                    }
                                                                    ?>
                                                                </small>
                                                            <?php endif; ?>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td><?php echo format_price($item['price']); ?></td>
                                                <td><?php echo $item['quantity']; ?></td>
                                                <td><?php echo format_price($item['price'] * $item['quantity']); ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6 ms-auto">
                                    <table class="table table-bordered">
                                        <tbody>
                                            <tr>
                                                <td><strong>Subtotal</strong></td>
                                                <td class="text-end"><?php echo format_price($order['subtotal']); ?></td>
                                            </tr>
                                            <?php if ($order['discount'] > 0): ?>
                                                <tr>
                                                    <td><strong>Discount</strong></td>
                                                    <td class="text-end text-success">-<?php echo format_price($order['discount']); ?></td>
                                                </tr>
                                            <?php endif; ?>
                                            <tr>
                                                <td><strong>Shipping</strong></td>
                                                <td class="text-end"><?php echo $order['shipping'] > 0 ? format_price($order['shipping']) : 'Free'; ?></td>
                                            </tr>
                                            <?php if ($order['tax'] > 0): ?>
                                                <tr>
                                                    <td><strong>Tax</strong></td>
                                                    <td class="text-end"><?php echo format_price($order['tax']); ?></td>
                                                </tr>
                                            <?php endif; ?>
                                            <tr>
                                                <td><strong>Total</strong></td>
                                                <td class="text-end"><strong><?php echo format_price($order['total']); ?></strong></td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="order-actions text-center">
                    <a href="index.php" class="btn btn-primary me-2">Continue Shopping</a>
                    <?php if (is_logged_in()): ?>
                        <a href="account_orders.php" class="btn btn-outline-secondary">View My Orders</a>
                    <?php endif; ?>
                </div>
                
                <div class="order-notes mt-4">
                    <div class="card">
                        <div class="card-body">
                            <h5>What Happens Next?</h5>
                            <ol>
                                <li>We'll send you a confirmation email with your order details.</li>
                                <li>Your order will be processed and prepared for shipping.</li>
                                <li>Once your order ships, we'll send you a shipping confirmation email with tracking information.</li>
                                <li>Your package will arrive within the estimated delivery timeframe.</li>
                            </ol>
                            <p class="mb-0">If you have any questions about your order, please <a href="contact.php">contact our customer service team</a>.</p>
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

// Function to get status color
function get_status_color($status) {
    switch ($status) {
        case 'pending':
            return 'warning';
        case 'processing':
            return 'info';
        case 'shipped':
            return 'primary';
        case 'delivered':
            return 'success';
        case 'cancelled':
            return 'danger';
        default:
            return 'secondary';
    }
}
?>
