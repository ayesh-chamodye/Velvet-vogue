<?php
// Include functions
require_once 'includes/functions.php';

// Check if user is logged in
if (!is_logged_in()) {
    set_message('Please login to access order details.', 'warning');
    redirect('login.php');
    exit;
}

// Get order ID from URL
$order_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Get order details
$order = get_order($order_id);

// Check if order exists and belongs to the current user
if (!$order || $order['user_id'] != $_SESSION['user_id']) {
    set_message('Order not found.', 'danger');
    redirect('account_orders.php');
    exit;
}

// Get order items
$order_items = get_order_items($order_id);

// Set page title
$page_title = 'Order Details #' . $order_id;
$meta_description = 'View your order details at Velvet Vogue - Fashion E-commerce Store';
$meta_keywords = 'order details, account, fashion, clothing, Velvet Vogue';

// Include header
include_once 'includes/header.php';
?>

<!-- Breadcrumb -->
<nav aria-label="breadcrumb" class="bg-light py-3">
    <div class="container">
        <ol class="breadcrumb mb-0">
            <li class="breadcrumb-item"><a href="index.php">Home</a></li>
            <li class="breadcrumb-item"><a href="account.php">My Account</a></li>
            <li class="breadcrumb-item"><a href="account_orders.php">My Orders</a></li>
            <li class="breadcrumb-item active" aria-current="page">Order #<?php echo $order_id; ?></li>
        </ol>
    </div>
</nav>

<!-- Order Details Section -->
<section class="order-details-page py-5">
    <div class="container">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1 class="mb-0">Order #<?php echo $order_id; ?></h1>
            <a href="account_orders.php" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left me-2"></i> Back to Orders
            </a>
        </div>
        
        <div class="card mb-4">
            <div class="card-header bg-white">
                <div class="row align-items-center">
                    <div class="col-md-6">
                        <h5 class="mb-0">Order Information</h5>
                    </div>
                    <div class="col-md-6 text-md-end">
                        <span class="badge bg-<?php echo get_status_color($order['status']); ?> me-2">
                            <?php echo ucfirst($order['status']); ?>
                        </span>
                        <span class="text-muted">
                            <?php echo date('F j, Y', strtotime($order['created_at'])); ?>
                        </span>
                    </div>
                </div>
            </div>
            <div class="card-body">
                <div class="row mb-4">
                    <div class="col-md-6 mb-3 mb-md-0">
                        <h6>Billing Address</h6>
                        <address class="mb-0">
                            <?php echo $order['first_name'] . ' ' . $order['last_name']; ?><br>
                            <?php echo $order['address']; ?><br>
                            <?php echo $order['city'] . ', ' . $order['state'] . ' ' . $order['zip']; ?><br>
                            <?php echo $order['country']; ?><br>
                            <strong>Email:</strong> <?php echo $order['email']; ?><br>
                            <strong>Phone:</strong> <?php echo $order['phone']; ?>
                        </address>
                    </div>
                    
                    <div class="col-md-6">
                        <h6>Shipping Address</h6>
                        <address class="mb-0">
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
                
                <div class="row">
                    <div class="col-md-6 mb-3 mb-md-0">
                        <h6>Payment Method</h6>
                        <p class="mb-0">
                            <?php echo ucfirst($order['payment_method']); ?>
                            <?php if ($order['payment_id']): ?>
                                <br><small class="text-muted">Transaction ID: <?php echo $order['payment_id']; ?></small>
                            <?php endif; ?>
                        </p>
                    </div>
                    
                    <div class="col-md-6">
                        <h6>Order Notes</h6>
                        <p class="mb-0">
                            <?php echo !empty($order['notes']) ? $order['notes'] : 'No notes provided'; ?>
                        </p>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="card mb-4">
            <div class="card-header bg-white">
                <h5 class="mb-0">Order Items</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Product</th>
                                <th>Price</th>
                                <th>Quantity</th>
                                <th class="text-end">Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($order_items as $item): ?>
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <img src="<?php echo !empty($item['image']) ? $item['image'] : 'assets/images/products/default.jpg'; ?>" alt="<?php echo $item['name']; ?>" class="order-item-image me-3" width="60">
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
                                    <td class="text-end"><?php echo format_price($item['price'] * $item['quantity']); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                        <tfoot>
                            <tr>
                                <td colspan="3" class="text-end"><strong>Subtotal</strong></td>
                                <td class="text-end"><?php echo format_price($order['subtotal']); ?></td>
                            </tr>
                            <?php if ($order['discount'] > 0): ?>
                                <tr>
                                    <td colspan="3" class="text-end"><strong>Discount</strong></td>
                                    <td class="text-end text-success">-<?php echo format_price($order['discount']); ?></td>
                                </tr>
                            <?php endif; ?>
                            <tr>
                                <td colspan="3" class="text-end"><strong>Shipping</strong></td>
                                <td class="text-end"><?php echo $order['shipping'] > 0 ? format_price($order['shipping']) : 'Free'; ?></td>
                            </tr>
                            <?php if ($order['tax'] > 0): ?>
                                <tr>
                                    <td colspan="3" class="text-end"><strong>Tax</strong></td>
                                    <td class="text-end"><?php echo format_price($order['tax']); ?></td>
                                </tr>
                            <?php endif; ?>
                            <tr>
                                <td colspan="3" class="text-end"><strong>Total</strong></td>
                                <td class="text-end"><strong><?php echo format_price($order['total']); ?></strong></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
        
        <div class="card mb-4">
            <div class="card-header bg-white">
                <h5 class="mb-0">Order Timeline</h5>
            </div>
            <div class="card-body">
                <ul class="timeline">
                    <li class="timeline-item">
                        <div class="timeline-marker bg-success"></div>
                        <div class="timeline-content">
                            <h6 class="mb-0">Order Placed</h6>
                            <p class="mb-0 text-muted"><?php echo date('F j, Y - h:i A', strtotime($order['created_at'])); ?></p>
                        </div>
                    </li>
                    
                    <?php if ($order['status'] != 'pending'): ?>
                        <li class="timeline-item">
                            <div class="timeline-marker bg-info"></div>
                            <div class="timeline-content">
                                <h6 class="mb-0">Payment <?php echo ($order['status'] == 'cancelled') ? 'Cancelled' : 'Confirmed'; ?></h6>
                                <p class="mb-0 text-muted"><?php echo date('F j, Y - h:i A', strtotime($order['updated_at'])); ?></p>
                            </div>
                        </li>
                    <?php endif; ?>
                    
                    <?php if (in_array($order['status'], ['processing', 'shipped', 'delivered'])): ?>
                        <li class="timeline-item">
                            <div class="timeline-marker bg-primary"></div>
                            <div class="timeline-content">
                                <h6 class="mb-0">Order Processing</h6>
                                <p class="mb-0 text-muted"><?php echo date('F j, Y - h:i A', strtotime('+1 day', strtotime($order['updated_at']))); ?></p>
                            </div>
                        </li>
                    <?php endif; ?>
                    
                    <?php if (in_array($order['status'], ['shipped', 'delivered'])): ?>
                        <li class="timeline-item">
                            <div class="timeline-marker bg-primary"></div>
                            <div class="timeline-content">
                                <h6 class="mb-0">Order Shipped</h6>
                                <p class="mb-0 text-muted"><?php echo date('F j, Y - h:i A', strtotime('+2 days', strtotime($order['updated_at']))); ?></p>
                                <?php if (!empty($order['tracking_number'])): ?>
                                    <p class="mb-0">Tracking Number: <?php echo $order['tracking_number']; ?></p>
                                <?php endif; ?>
                            </div>
                        </li>
                    <?php endif; ?>
                    
                    <?php if ($order['status'] == 'delivered'): ?>
                        <li class="timeline-item">
                            <div class="timeline-marker bg-success"></div>
                            <div class="timeline-content">
                                <h6 class="mb-0">Order Delivered</h6>
                                <p class="mb-0 text-muted"><?php echo date('F j, Y - h:i A', strtotime('+5 days', strtotime($order['updated_at']))); ?></p>
                            </div>
                        </li>
                    <?php endif; ?>
                    
                    <?php if ($order['status'] == 'cancelled'): ?>
                        <li class="timeline-item">
                            <div class="timeline-marker bg-danger"></div>
                            <div class="timeline-content">
                                <h6 class="mb-0">Order Cancelled</h6>
                                <p class="mb-0 text-muted"><?php echo date('F j, Y - h:i A', strtotime($order['updated_at'])); ?></p>
                            </div>
                        </li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
        
        <div class="order-actions d-flex justify-content-between">
            <a href="account_orders.php" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left me-2"></i> Back to Orders
            </a>
            
            <div>
                <?php if ($order['status'] == 'delivered'): ?>
                    <a href="review.php?order_id=<?php echo $order_id; ?>" class="btn btn-outline-primary me-2">
                        <i class="fas fa-star me-2"></i> Write a Review
                    </a>
                <?php endif; ?>
                
                <a href="contact.php?subject=Order%20#<?php echo $order_id; ?>" class="btn btn-primary">
                    <i class="fas fa-question-circle me-2"></i> Need Help?
                </a>
            </div>
        </div>
    </div>
</section>

<style>
.order-item-image {
    width: 60px;
    height: 60px;
    object-fit: cover;
}

.timeline {
    position: relative;
    list-style: none;
    padding: 0;
    margin: 0;
}

.timeline-item {
    position: relative;
    padding-left: 40px;
    margin-bottom: 25px;
}

.timeline-item:last-child {
    margin-bottom: 0;
}

.timeline-marker {
    position: absolute;
    left: 0;
    top: 0;
    width: 20px;
    height: 20px;
    border-radius: 50%;
}

.timeline-content {
    padding-bottom: 10px;
}

.timeline:before {
    content: '';
    position: absolute;
    left: 10px;
    top: 0;
    height: 100%;
    width: 2px;
    background-color: #e9ecef;
}
</style>

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
        case 'returned':
            return 'secondary';
        default:
            return 'secondary';
    }
}
?>
