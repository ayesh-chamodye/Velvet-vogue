<?php
session_start();
require_once '../config/database.php';
require_once '../includes/functions.php';

// Check if admin is logged in
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: login.php');
    exit;
}

// Handle customer actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'update_status':
                $customer_id = (int)$_POST['customer_id'];
                $status = clean_input($_POST['status']);
                
                $sql = "UPDATE users SET status = ? WHERE id = ? AND role = 'customer'";
                $stmt = mysqli_prepare($conn, $sql);
                mysqli_stmt_bind_param($stmt, "si", $status, $customer_id);
                
                if (mysqli_stmt_execute($stmt)) {
                    $success_message = "Customer status updated!";
                }
                break;
        }
    }
}

// Get filter parameters
$status_filter = isset($_GET['status']) ? clean_input($_GET['status']) : '';

// Build query
$sql = "SELECT u.*, 
        COUNT(o.id) as total_orders,
        SUM(o.total_amount) as total_spent
        FROM users u 
        LEFT JOIN orders o ON u.id = o.user_id AND o.payment_status = 'paid'
        WHERE u.role = 'customer'";

if (!empty($status_filter)) {
    $sql .= " AND u.status = '$status_filter'";
}

$sql .= " GROUP BY u.id ORDER BY u.created_at DESC";
$customers = mysqli_query($conn, $sql);

$page_title = "Customers Management";
include 'includes/header.php';
?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Customers Management</h1>
</div>

<?php if (isset($success_message)): ?>
    <div class="alert alert-success"><?php echo $success_message; ?></div>
<?php endif; ?>

<!-- Filters -->
<div class="card mb-4">
    <div class="card-body">
        <form method="GET" class="row g-3">
            <div class="col-md-3">
                <select name="status" class="form-select">
                    <option value="">All Status</option>
                    <option value="active" <?php echo $status_filter == 'active' ? 'selected' : ''; ?>>Active</option>
                    <option value="inactive" <?php echo $status_filter == 'inactive' ? 'selected' : ''; ?>>Inactive</option>
                    <option value="blocked" <?php echo $status_filter == 'blocked' ? 'selected' : ''; ?>>Blocked</option>
                </select>
            </div>
            <div class="col-md-3">
                <button type="submit" class="btn btn-purple">Filter</button>
                <a href="customers.php" class="btn btn-outline-secondary">Reset</a>
            </div>
        </form>
    </div>
</div>

<!-- Customers Table -->
<div class="card">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Customer</th>
                        <th>Email</th>
                        <th>Phone</th>
                        <th>Orders</th>
                        <th>Total Spent</th>
                        <th>Status</th>
                        <th>Registered</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($customer = mysqli_fetch_assoc($customers)): ?>
                        <tr>
                            <td>
                                <strong><?php echo htmlspecialchars($customer['username']); ?></strong><br>
                                <small class="text-muted">
                                    <?php echo htmlspecialchars($customer['first_name'] . ' ' . $customer['last_name']); ?>
                                </small>
                            </td>
                            <td><?php echo htmlspecialchars($customer['email']); ?></td>
                            <td><?php echo htmlspecialchars($customer['phone'] ?? 'N/A'); ?></td>
                            <td>
                                <?php if ($customer['total_orders'] > 0): ?>
                                    <a href="orders.php?search=<?php echo urlencode($customer['email']); ?>" class="badge bg-info text-decoration-none">
                                        <?php echo $customer['total_orders']; ?> orders
                                    </a>
                                <?php else: ?>
                                    <span class="badge bg-secondary">No orders</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if ($customer['total_spent'] > 0): ?>
                                    $<?php echo number_format($customer['total_spent'], 2); ?>
                                <?php else: ?>
                                    $0.00
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if ($customer['status'] == 'active'): ?>
                                    <span class="badge bg-success status-badge">Active</span>
                                <?php else: ?>
                                    <span class="badge bg-danger status-badge">Inactive</span>
                                <?php endif; ?>
                            </td>
                            <td><?php echo date('M d, Y', strtotime($customer['created_at'])); ?></td>
                            <td>
                                <div class="btn-group">
                                    <a href="customer-details.php?id=<?php echo $customer['id']; ?>" class="btn btn-sm btn-outline-primary" title="View Details">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <button type="button" class="btn btn-sm btn-outline-secondary" 
                                            data-bs-toggle="modal" data-bs-target="#updateStatusModal<?php echo $customer['id']; ?>" title="Update Status">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                </div>
                                
                                <!-- Update Status Modal -->
                                <div class="modal fade" id="updateStatusModal<?php echo $customer['id']; ?>" tabindex="-1" aria-hidden="true">
                                    <div class="modal-dialog">
                                        <div class="modal-content">
                                            <form action="" method="post">
                                                <input type="hidden" name="customer_id" value="<?php echo $customer['id']; ?>">
                                                <div class="modal-header">
                                                    <h5 class="modal-title">Update Customer Status</h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                </div>
                                                <div class="modal-body">
                                                    <p>Customer: <strong><?php echo htmlspecialchars($customer['username']); ?></strong></p>
                                                    <div class="mb-3">
                                                        <label for="status<?php echo $customer['id']; ?>" class="form-label">Status</label>
                                                        <select class="form-select" id="status<?php echo $customer['id']; ?>" name="status">
                                                            <option value="active" <?php echo ($customer['status'] == 'active') ? 'selected' : ''; ?>>Active</option>
                                                            <option value="inactive" <?php echo ($customer['status'] == 'inactive') ? 'selected' : ''; ?>>Inactive</option>
                                                        </select>
                                                    </div>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                                    <button type="submit" name="action" value="update_status" class="btn btn-primary">Update Status</button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?php include 'includes/footer.php'; ?>

