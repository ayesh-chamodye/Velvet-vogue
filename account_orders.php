<?php
// Include functions
require_once 'includes/functions.php';

// Check if user is logged in
if (!is_logged_in()) {
    set_message('Please login to access your orders.', 'warning');
    redirect('login.php');
    exit;
}

// Get user information
$user_id = $_SESSION['user_id'];
$user = get_user($user_id);

// Get user orders
$orders = get_user_orders($user_id);

// Set page title
$page_title = 'My Orders';
$meta_description = 'View your order history at Velvet Vogue - Fashion E-commerce Store';
$meta_keywords = 'orders, history, account, fashion, clothing, Velvet Vogue';

// Include header
include_once 'includes/header.php';
?>

<!-- Breadcrumb -->
<nav aria-label="breadcrumb" class="bg-light py-3">
    <div class="container">
        <ol class="breadcrumb mb-0">
            <li class="breadcrumb-item"><a href="index.php">Home</a></li>
            <li class="breadcrumb-item"><a href="account.php">My Account</a></li>
            <li class="breadcrumb-item active" aria-current="page">My Orders</li>
        </ol>
    </div>
</nav>

<!-- Account Orders Section -->
<section class="account-orders-page py-5">
    <div class="container">
        <h1 class="mb-4">My Orders</h1>
        
        <div class="row">
            <!-- Account Sidebar -->
            <div class="col-lg-3 mb-4 mb-lg-0">
                <div class="account-sidebar">
                    <div class="card mb-4">
                        <div class="card-body">
                            <div class="account-user text-center mb-4">
                                <div class="account-avatar mb-3">
                                    <i class="fas fa-user-circle fa-5x text-secondary"></i>
                                </div>
                                <h5 class="mb-1"><?php echo $user['first_name'] . ' ' . $user['last_name']; ?></h5>
                                <p class="text-muted mb-0"><?php echo $user['email']; ?></p>
                            </div>
                            
                            <div class="account-nav">
                                <ul class="nav flex-column">
                                    <li class="nav-item">
                                        <a class="nav-link" href="account.php">
                                            <i class="fas fa-user me-2"></i> Profile
                                        </a>
                                    </li>
                                    <li class="nav-item">
                                        <a class="nav-link active" href="account_orders.php">
                                            <i class="fas fa-shopping-bag me-2"></i> Orders
                                        </a>
                                    </li>
                                    <li class="nav-item">
                                        <a class="nav-link" href="wishlist.php">
                                            <i class="fas fa-heart me-2"></i> Wishlist
                                        </a>
                                    </li>
                                    <li class="nav-item">
                                        <a class="nav-link" href="account_addresses.php">
                                            <i class="fas fa-map-marker-alt me-2"></i> Addresses
                                        </a>
                                    </li>
                                    <li class="nav-item">
                                        <a class="nav-link" href="logout.php">
                                            <i class="fas fa-sign-out-alt me-2"></i> Logout
                                        </a>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>
                    
                    <div class="card">
                        <div class="card-body">
                            <h5 class="mb-3">Need Help?</h5>
                            <p class="mb-3">If you have any questions about your orders, please contact our customer support team.</p>
                            <a href="contact.php" class="btn btn-outline-primary btn-sm">Contact Support</a>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Account Orders Content -->
            <div class="col-lg-9">
                <div class="card">
                    <div class="card-header bg-white d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">Order History</h5>
                        
                        <?php if (!empty($orders)): ?>
                            <span class="badge bg-primary"><?php echo count($orders); ?> Orders</span>
                        <?php endif; ?>
                    </div>
                    <div class="card-body">
                        <?php if (empty($orders)): ?>
                            <div class="text-center py-5">
                                <i class="fas fa-shopping-bag fa-4x text-muted mb-3"></i>
                                <h5>No Orders Found</h5>
                                <p class="text-muted">You haven't placed any orders yet.</p>
                                <a href="products.php" class="btn btn-primary mt-3">Start Shopping</a>
                            </div>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>Order #</th>
                                            <th>Date</th>
                                            <th>Status</th>
                                            <th>Total</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($orders as $order): ?>
                                            <tr>
                                                <td>#<?php echo $order['id']; ?></td>
                                                <td><?php echo date('M d, Y', strtotime($order['created_at'])); ?></td>
                                                <td>
                                                    <span class="badge bg-<?php echo get_status_color($order['status']); ?>">
                                                        <?php echo ucfirst($order['status']); ?>
                                                    </span>
                                                </td>
                                                <td><?php echo format_price($order['total']); ?></td>
                                                <td>
                                                    <a href="order_details.php?id=<?php echo $order['id']; ?>" class="btn btn-sm btn-outline-primary">
                                                        View Details
                                                    </a>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
                
                <div class="order-info mt-4">
                    <div class="card">
                        <div class="card-body">
                            <h5 class="mb-3">Order Status Guide</h5>
                            <div class="row">
                                <div class="col-md-6">
                                    <ul class="list-unstyled">
                                        <li class="mb-2">
                                            <span class="badge bg-warning me-2">Pending</span>
                                            Order received, awaiting payment confirmation
                                        </li>
                                        <li class="mb-2">
                                            <span class="badge bg-info me-2">Processing</span>
                                            Payment confirmed, preparing your order
                                        </li>
                                        <li class="mb-2">
                                            <span class="badge bg-primary me-2">Shipped</span>
                                            Order has been shipped, on its way to you
                                        </li>
                                    </ul>
                                </div>
                                <div class="col-md-6">
                                    <ul class="list-unstyled">
                                        <li class="mb-2">
                                            <span class="badge bg-success me-2">Delivered</span>
                                            Order has been delivered successfully
                                        </li>
                                        <li class="mb-2">
                                            <span class="badge bg-danger me-2">Cancelled</span>
                                            Order has been cancelled
                                        </li>
                                        <li class="mb-2">
                                            <span class="badge bg-secondary me-2">Returned</span>
                                            Order has been returned
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="order-help mt-4">
                    <div class="card">
                        <div class="card-body">
                            <h5 class="mb-3">Order Help</h5>
                            <p>Need help with an order? Here are some quick links:</p>
                            <div class="row">
                                <div class="col-md-4 mb-3 mb-md-0">
                                    <a href="contact.php" class="btn btn-outline-secondary w-100">
                                        <i class="fas fa-question-circle me-2"></i> Order Support
                                    </a>
                                </div>
                                <div class="col-md-4 mb-3 mb-md-0">
                                    <a href="returns.php" class="btn btn-outline-secondary w-100">
                                        <i class="fas fa-undo me-2"></i> Returns & Exchanges
                                    </a>
                                </div>
                                <div class="col-md-4">
                                    <a href="shipping.php" class="btn btn-outline-secondary w-100">
                                        <i class="fas fa-truck me-2"></i> Shipping Info
                                    </a>
                                </div>
                            </div>
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
        case 'returned':
            return 'secondary';
        default:
            return 'secondary';
    }
}
?>
