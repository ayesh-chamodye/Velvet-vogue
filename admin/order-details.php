<?php
session_start();
require_once '../config/database.php';
require_once '../includes/functions.php';

// Check if admin is logged in
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: login.php');
    exit;
}

$success_message = '';
$error_message = '';

// Check if order ID is provided
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header('Location: orders.php');
    exit;
}

$order_id = (int)$_GET['id'];

// Handle order status update
if (isset($_POST['update_status']) && isset($_POST['status'])) {
    $status = clean_input($_POST['status']);
    
    $sql = "UPDATE orders SET order_status = ?, updated_at = NOW() WHERE id = ?";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "si", $status, $order_id);
    
    if (mysqli_stmt_execute($stmt)) {
        $success_message = "Order status updated successfully!";
    } else {
        $error_message = "Failed to update order status. Error: " . mysqli_error($conn);
    }
}

// Handle payment status update
if (isset($_POST['update_payment']) && isset($_POST['payment_status'])) {
    $payment_status = clean_input($_POST['payment_status']);
    
    $sql = "UPDATE orders SET payment_status = ?, updated_at = NOW() WHERE id = ?";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "si", $payment_status, $order_id);
    
    if (mysqli_stmt_execute($stmt)) {
        $success_message = "Payment status updated successfully!";
    } else {
        $error_message = "Failed to update payment status. Error: " . mysqli_error($conn);
    }
}

// Get order details
$sql = "SELECT o.*, u.username, u.email, u.phone 
        FROM orders o 
        LEFT JOIN users u ON o.user_id = u.id 
        WHERE o.id = ?";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "i", $order_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

if (mysqli_num_rows($result) === 0) {
    header('Location: orders.php');
    exit;
}

$order = mysqli_fetch_assoc($result);

// Get order items
$sql = "SELECT oi.*, p.name as product_name, p.sku 
        FROM order_items oi 
        JOIN products p ON oi.product_id = p.id 
        WHERE oi.order_id = ?";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "i", $order_id);
mysqli_stmt_execute($stmt);
$order_items = mysqli_stmt_get_result($stmt);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Details #<?php echo $order_id; ?> - Velvet Vogue Admin</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <!-- Custom CSS -->
    <style>
        :root {
            --primary-color: #6a1b9a;
            --secondary-color: #9c27b0;
            --light-color: #f3e5f5;
            --dark-color: #4a148c;
            --success-color: #4caf50;
            --warning-color: #ff9800;
            --danger-color: #f44336;
            --info-color: #2196f3;
        }
        
        .sidebar {
            background-color: var(--primary-color);
            min-height: 100vh;
            color: white;
        }
        
        .sidebar .nav-link {
            color: rgba(255, 255, 255, 0.8);
            padding: 0.75rem 1rem;
            border-radius: 0.25rem;
            margin-bottom: 0.25rem;
        }
        
        .sidebar .nav-link:hover, .sidebar .nav-link.active {
            color: white;
            background-color: rgba(255, 255, 255, 0.1);
        }
        
        .sidebar .nav-link i {
            margin-right: 0.5rem;
        }
        
        .brand {
            background-color: var(--dark-color);
            padding: 1rem;
        }
        
        .content-wrapper {
            background-color: #f8f9fa;
        }
        
        .btn-purple {
            background-color: var(--primary-color);
            color: white;
        }
        
        .btn-purple:hover {
            background-color: var(--dark-color);
            color: white;
        }
        
        .status-badge {
            font-size: 0.85rem;
            padding: 0.35rem 0.65rem;
        }
        
        .order-summary {
            background-color: #f8f9fa;
            border-radius: 0.5rem;
            padding: 1.5rem;
        }
        
        .product-img-thumbnail {
            width: 60px;
            height: 60px;
            object-fit: cover;
            border-radius: 4px;
        }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <div class="col-md-3 col-lg-2 d-md-block sidebar collapse">
                <div class="brand d-flex align-items-center mb-3">
                    <h4 class="mb-0">Velvet Vogue</h4>
                </div>
                <div class="d-flex flex-column p-3">
                    <div class="text-center mb-3">
                        <div class="mb-2">
                            <i class="fas fa-user-circle fa-3x"></i>
                        </div>
                        <h6 class="mb-0"><?php echo htmlspecialchars($_SESSION['admin_username']); ?></h6>
                        <small>Administrator</small>
                    </div>
                    <hr>
                    <ul class="nav flex-column">
                        <li class="nav-item">
                            <a class="nav-link" href="index.php">
                                <i class="fas fa-tachometer-alt"></i> Dashboard
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="products.php">
                                <i class="fas fa-box"></i> Products
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="categories.php">
                                <i class="fas fa-tags"></i> Categories
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link active" href="orders.php">
                                <i class="fas fa-shopping-cart"></i> Orders
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="customers.php">
                                <i class="fas fa-users"></i> Customers
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="banners.php">
                                <i class="fas fa-images"></i> Banners
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="reviews.php">
                                <i class="fas fa-star"></i> Reviews
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="reports.php">
                                <i class="fas fa-chart-bar"></i> Reports
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="settings.php">
                                <i class="fas fa-cog"></i> Settings
                            </a>
                        </li>
                        <li class="nav-item mt-3">
                            <a class="nav-link bg-danger text-white" href="logout.php">
                                <i class="fas fa-sign-out-alt"></i> Logout
                            </a>
                        </li>
                    </ul>
                </div>
            </div>
            
            <!-- Main Content -->
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 content-wrapper">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h2">Order Details #<?php echo $order_id; ?></h1>
                    <div class="btn-toolbar mb-2 mb-md-0">
                        <a href="orders.php" class="btn btn-sm btn-outline-secondary">
                            <i class="fas fa-arrow-left"></i> Back to Orders
                        </a>
                    </div>
                </div>
                
                <?php if (!empty($success_message)): ?>
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <?php echo $success_message; ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                <?php endif; ?>
                
                <?php if (!empty($error_message)): ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <?php echo $error_message; ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                <?php endif; ?>
                
                <div class="row mb-4">
                    <!-- Order Information -->
                    <div class="col-md-6 mb-4">
                        <div class="card h-100">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <h5 class="mb-0">Order Information</h5>
                                <button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#updateStatusModal">
                                    <i class="fas fa-edit"></i> Update Status
                                </button>
                            </div>
                            <div class="card-body">
                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <p class="mb-1"><strong>Order ID:</strong> #<?php echo $order_id; ?></p>
                                        <p class="mb-1"><strong>Date:</strong> <?php echo date('M d, Y H:i', strtotime($order['created_at'])); ?></p>
                                        <p class="mb-1">
                                            <strong>Status:</strong>
                                            <?php
                                            $status_class = '';
                                            switch ($order['order_status']) {
                                                case 'pending':
                                                    $status_class = 'bg-warning';
                                                    break;
                                                case 'processing':
                                                    $status_class = 'bg-info';
                                                    break;
                                                case 'shipped':
                                                    $status_class = 'bg-primary';
                                                    break;
                                                case 'delivered':
                                                    $status_class = 'bg-success';
                                                    break;
                                                case 'cancelled':
                                                    $status_class = 'bg-danger';
                                                    break;
                                            }
                                            ?>
                                            <span class="badge <?php echo $status_class; ?> status-badge">
                                                <?php echo ucfirst($order['order_status']); ?>
                                            </span>
                                        </p>
                                    </div>
                                    <div class="col-md-6">
                                        <p class="mb-1">
                                            <strong>Payment Status:</strong>
                                            <?php if ($order['payment_status'] == 'paid'): ?>
                                                <span class="badge bg-success status-badge">Paid</span>
                                            <?php else: ?>
                                                <span class="badge bg-secondary status-badge">Pending</span>
                                            <?php endif; ?>
                                            <button class="btn btn-sm btn-link p-0 ms-2" data-bs-toggle="modal" data-bs-target="#updatePaymentModal">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                        </p>
                                        <p class="mb-1"><strong>Payment Method:</strong> <?php echo ucfirst($order['payment_method']); ?></p>
                                        <p class="mb-1"><strong>Total Amount:</strong> $<?php echo number_format($order['total_amount'], 2); ?></p>
                                    </div>
                                </div>
                                
                                <?php if (!empty($order['notes'])): ?>
                                    <div class="mb-3">
                                        <h6>Order Notes:</h6>
                                        <p class="mb-0"><?php echo nl2br(htmlspecialchars($order['notes'])); ?></p>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Customer Information -->
                    <div class="col-md-6 mb-4">
                        <div class="card h-100">
                            <div class="card-header">
                                <h5 class="mb-0">Customer Information</h5>
                            </div>
                            <div class="card-body">
                                <?php if (!empty($order['user_id'])): ?>
                                    <div class="mb-3">
                                        <h6>Account Details:</h6>
                                        <p class="mb-1"><strong>Name:</strong> <?php echo htmlspecialchars($order['username']); ?></p>
                                        <p class="mb-1"><strong>Email:</strong> <?php echo htmlspecialchars($order['email']); ?></p>
                                        <?php if (!empty($order['phone'])): ?>
                                            <p class="mb-1"><strong>Phone:</strong> <?php echo htmlspecialchars($order['phone']); ?></p>
                                        <?php endif; ?>
                                        <a href="customer-details.php?id=<?php echo $order['user_id']; ?>" class="btn btn-sm btn-outline-info mt-2">
                                            <i class="fas fa-user"></i> View Customer Profile
                                        </a>
                                    </div>
                                <?php else: ?>
                                    <p>Order placed as guest</p>
                                <?php endif; ?>
                                
                                <div class="mt-4">
                                    <h6>Shipping Address:</h6>
                                    <address>
                                        <?php echo nl2br(htmlspecialchars($order['shipping_address'])); ?>
                                    </address>
                                </div>
                                
                                <?php if (!empty($order['billing_address']) && $order['billing_address'] != $order['shipping_address']): ?>
                                    <div class="mt-4">
                                        <h6>Billing Address:</h6>
                                        <address>
                                            <?php echo nl2br(htmlspecialchars($order['billing_address'])); ?>
                                        </address>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Order Items -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">Order Items</h5>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Product</th>
                                        <th>SKU</th>
                                        <th>Price</th>
                                        <th>Quantity</th>
                                        <th>Total</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php 
                                    $subtotal = 0;
                                    while ($item = mysqli_fetch_assoc($order_items)): 
                                        $item_total = $item['price'] * $item['quantity'];
                                        $subtotal += $item_total;
                                    ?>
                                        <tr>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <?php
                                                    // Get product image
                                                    $img_sql = "SELECT image_url FROM product_images WHERE product_id = ? AND is_primary = 1 LIMIT 1";
                                                    $img_stmt = mysqli_prepare($conn, $img_sql);
                                                    mysqli_stmt_bind_param($img_stmt, "i", $item['product_id']);
                                                    mysqli_stmt_execute($img_stmt);
                                                    $img_result = mysqli_stmt_get_result($img_stmt);
                                                    $img_url = '../assets/img/products/default-product.jpg';
                                                    
                                                    if ($img_row = mysqli_fetch_assoc($img_result)) {
                                                        $img_url = '../' . $img_row['image_url'];
                                                    }
                                                    ?>
                                                    <img src="<?php echo $img_url; ?>" alt="<?php echo htmlspecialchars($item['product_name']); ?>" class="product-img-thumbnail me-3">
                                                    <div>
                                                        <h6 class="mb-0"><?php echo htmlspecialchars($item['product_name']); ?></h6>
                                                        <?php if (!empty($item['attributes'])): ?>
                                                            <?php 
                                                            $attributes = json_decode($item['attributes'], true);
                                                            if ($attributes && is_array($attributes)): 
                                                            ?>
                                                                <small class="text-muted">
                                                                    <?php 
                                                                    $attr_strings = [];
                                                                    foreach ($attributes as $key => $value) {
                                                                        $attr_strings[] = htmlspecialchars($key) . ': ' . htmlspecialchars($value);
                                                                    }
                                                                    echo implode(', ', $attr_strings);
                                                                    ?>
                                                                </small>
                                                            <?php endif; ?>
                                                        <?php endif; ?>
                                                    </div>
                                                </div>
                                            </td>
                                            <td><?php echo htmlspecialchars($item['sku'] ?? 'N/A'); ?></td>
                                            <td>$<?php echo number_format($item['price'], 2); ?></td>
                                            <td><?php echo $item['quantity']; ?></td>
                                            <td>$<?php echo number_format($item_total, 2); ?></td>
                                        </tr>
                                    <?php endwhile; ?>
                                </tbody>
                                <tfoot class="table-light">
                                    <tr>
                                        <td colspan="4" class="text-end"><strong>Subtotal:</strong></td>
                                        <td>$<?php echo number_format($subtotal, 2); ?></td>
                                    </tr>
                                    <?php if ($order['discount_amount'] > 0): ?>
                                        <tr>
                                            <td colspan="4" class="text-end"><strong>Discount:</strong></td>
                                            <td>-$<?php echo number_format($order['discount_amount'], 2); ?></td>
                                        </tr>
                                    <?php endif; ?>
                                    <tr>
                                        <td colspan="4" class="text-end"><strong>Shipping:</strong></td>
                                        <td>$<?php echo number_format($order['shipping_fee'], 2); ?></td>
                                    </tr>
                                    <tr>
                                        <td colspan="4" class="text-end"><strong>Tax:</strong></td>
                                        <td>$<?php echo number_format($order['tax_amount'], 2); ?></td>
                                    </tr>
                                    <tr>
                                        <td colspan="4" class="text-end"><strong>Total:</strong></td>
                                        <td><strong>$<?php echo number_format($order['total_amount'], 2); ?></strong></td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                </div>
                
                <!-- Print Invoice Button -->
                <div class="text-end mb-4">
                    <a href="invoice-print.php?id=<?php echo $order_id; ?>" target="_blank" class="btn btn-purple">
                        <i class="fas fa-print"></i> Print Invoice
                    </a>
                </div>
            </main>
        </div>
    </div>
    
    <!-- Update Status Modal -->
    <div class="modal fade" id="updateStatusModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form action="" method="post">
                    <div class="modal-header">
                        <h5 class="modal-title">Update Order Status</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="status" class="form-label">Status</label>
                            <select class="form-select" id="status" name="status">
                                <option value="pending" <?php echo ($order['order_status'] == 'pending') ? 'selected' : ''; ?>>Pending</option>
                                <option value="processing" <?php echo ($order['order_status'] == 'processing') ? 'selected' : ''; ?>>Processing</option>
                                <option value="shipped" <?php echo ($order['order_status'] == 'shipped') ? 'selected' : ''; ?>>Shipped</option>
                                <option value="delivered" <?php echo ($order['order_status'] == 'delivered') ? 'selected' : ''; ?>>Delivered</option>
                                <option value="cancelled" <?php echo ($order['order_status'] == 'cancelled') ? 'selected' : ''; ?>>Cancelled</option>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" name="update_status" class="btn btn-primary">Update Status</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <!-- Update Payment Modal -->
    <div class="modal fade" id="updatePaymentModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form action="" method="post">
                    <div class="modal-header">
                        <h5 class="modal-title">Update Payment Status</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="payment_status" class="form-label">Payment Status</label>
                            <select class="form-select" id="payment_status" name="payment_status">
                                <option value="pending" <?php echo ($order['payment_status'] == 'pending') ? 'selected' : ''; ?>>Pending</option>
                                <option value="paid" <?php echo ($order['payment_status'] == 'paid') ? 'selected' : ''; ?>>Paid</option>
                                <option value="refunded" <?php echo ($order['payment_status'] == 'refunded') ? 'selected' : ''; ?>>Refunded</option>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" name="update_payment" class="btn btn-primary">Update Payment</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
