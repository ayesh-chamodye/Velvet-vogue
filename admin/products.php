<?php
session_start();
require_once '../config/database.php';
require_once '../includes/functions.php';

// Check if admin is logged in
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: login.php');
    exit;
}

// Handle product actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'add':
                $name = clean_input($_POST['name']);
                $description = clean_input($_POST['description']);
                $price = (float)$_POST['price'];
                $category_id = (int)$_POST['category_id'];
                $stock_quantity = (int)$_POST['stock_quantity'];
                $status = clean_input($_POST['status']);
                
                // Handle image upload
                $image_path = '';
                if (isset($_FILES['image']) && $_FILES['image']['error'] === 0) {
                    $upload_dir = '../uploads/products/';
                    if (!file_exists($upload_dir)) {
                        mkdir($upload_dir, 0777, true);
                    }
                    
                    $file_extension = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
                    $filename = uniqid() . '.' . $file_extension;
                    $image_path = 'uploads/products/' . $filename;
                    
                    move_uploaded_file($_FILES['image']['tmp_name'], $upload_dir . $filename);
                }
                
                $sql = "INSERT INTO products (name, description, price, category_id, stock_quantity, status, created_at) VALUES (?, ?, ?, ?, ?, ?, NOW())";
                $stmt = mysqli_prepare($conn, $sql);
                mysqli_stmt_bind_param($stmt, "ssdiis", $name, $description, $price, $category_id, $stock_quantity, $status);
                
                if (mysqli_stmt_execute($stmt)) {
                    $success_message = "Product added successfully!";
                    
                    // After insert, if image uploaded, insert into product_images
                    if ($image_path) {
                        $product_id = mysqli_insert_id($conn);
                        $sql_img = "INSERT INTO product_images (product_id, image_url, is_primary) VALUES (?, ?, 1)";
                        $stmt_img = mysqli_prepare($conn, $sql_img);
                        mysqli_stmt_bind_param($stmt_img, "is", $product_id, $image_path);
                        mysqli_stmt_execute($stmt_img);
                    }
                } else {
                    $error_message = "Error adding product.";
                }
                break;
                
            case 'update_status':
                $product_id = (int)$_POST['product_id'];
                $status = clean_input($_POST['status']);
                
                $sql = "UPDATE products SET status = ? WHERE id = ?";
                $stmt = mysqli_prepare($conn, $sql);
                mysqli_stmt_bind_param($stmt, "si", $status, $product_id);
                
                if (mysqli_stmt_execute($stmt)) {
                    $success_message = "Product status updated!";
                }
                break;
                
            case 'delete':
                $product_id = (int)$_POST['product_id'];
                
                // Delete from product_images
                $sql = "DELETE FROM product_images WHERE product_id = ?";
                $stmt = mysqli_prepare($conn, $sql);
                mysqli_stmt_bind_param($stmt, "i", $product_id);
                mysqli_stmt_execute($stmt);
                
                // Delete product
                $sql = "DELETE FROM products WHERE id = ?";
                $stmt = mysqli_prepare($conn, $sql);
                mysqli_stmt_bind_param($stmt, "i", $product_id);
                
                if (mysqli_stmt_execute($stmt)) {
                    $success_message = "Product deleted successfully!";
                }
                break;
        }
    }
}

// Get filter parameters
$category_filter = isset($_GET['category']) ? (int)$_GET['category'] : 0;
$status_filter = isset($_GET['status']) ? clean_input($_GET['status']) : '';

// Build query
$sql = "SELECT p.*, c.name as category_name 
        FROM products p 
        LEFT JOIN categories c ON p.category_id = c.id 
        WHERE 1=1";

if ($category_filter > 0) {
    $sql .= " AND p.category_id = $category_filter";
}

if (!empty($status_filter)) {
    $sql .= " AND p.status = '$status_filter'";
}

$sql .= " ORDER BY p.created_at DESC";
$products = mysqli_query($conn, $sql);

// Get categories for dropdown
$categories = mysqli_query($conn, "SELECT * FROM categories ORDER BY name");

$page_title = "Products Management";
include 'includes/header.php';
?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Products Management</h1>
    <button class="btn btn-sm btn-purple" data-bs-toggle="modal" data-bs-target="#addProductModal">
        <i class="fas fa-plus"></i> Add New Product
    </button>
</div>

<?php if (isset($success_message)): ?>
    <div class="alert alert-success"><?php echo $success_message; ?></div>
<?php endif; ?>

<?php if (isset($error_message)): ?>
    <div class="alert alert-danger"><?php echo $error_message; ?></div>
<?php endif; ?>

<!-- Filters -->
<div class="card mb-4">
    <div class="card-body">
        <form method="GET" class="row g-3">
            <div class="col-md-3">
                <select name="category" class="form-select">
                    <option value="">All Categories</option>
                    <?php 
                    mysqli_data_seek($categories, 0);
                    while ($category = mysqli_fetch_assoc($categories)): 
                    ?>
                        <option value="<?php echo $category['id']; ?>" <?php echo $category_filter == $category['id'] ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($category['name']); ?>
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>
            <div class="col-md-3">
                <select name="status" class="form-select">
                    <option value="">All Status</option>
                    <option value="active" <?php echo $status_filter == 'active' ? 'selected' : ''; ?>>Active</option>
                    <option value="inactive" <?php echo $status_filter == 'inactive' ? 'selected' : ''; ?>>Inactive</option>
                </select>
            </div>
            <div class="col-md-3">
                <button type="submit" class="btn btn-purple">Filter</button>
                <a href="products.php" class="btn btn-outline-secondary">Reset</a>
            </div>
        </form>
    </div>
</div>

<!-- Products Table -->
<div class="card">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Image</th>
                        <th>Name</th>
                        <th>Category</th>
                        <th>Price</th>
                        <th>Stock</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($product = mysqli_fetch_assoc($products)): ?>
                        <tr>
                            <td>
                                <?php 
                                    $images = function_exists('get_product_images') ? get_product_images($product['id']) : [];
                                    $img_path = (!empty($images) && !empty($images[0]['image_url']) && file_exists('../' . $images[0]['image_url']))
                                        ? '../' . $images[0]['image_url']
                                        : '../assets/images/default-product.jpg';
                                ?>
                                <img src="<?php echo $img_path; ?>" alt="Product" style="width: 50px; height: 50px; object-fit: cover;">
                            </td>
                            <td><?php echo htmlspecialchars($product['name']); ?></td>
                            <td><?php echo htmlspecialchars($product['category_name']); ?></td>
                            <td>$<?php echo number_format($product['price'], 2); ?></td>
                            <td>
                                <span class="badge <?php echo $product['stock'] <= 10 ? 'bg-danger' : 'bg-success'; ?>">
                                    <?php echo $product['stock']; ?>
                                </span>
                            </td>
                            <td>
                                <span class="badge <?php echo $product['status'] == 'active' ? 'bg-success' : 'bg-secondary'; ?>">
                                    <?php echo ucfirst($product['status']); ?>
                                </span>
                            </td>
                            <td>
                                <div class="btn-group">
                                    <button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#editProductModal<?php echo $product['id']; ?>">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button class="btn btn-sm btn-outline-danger" data-bs-toggle="modal" data-bs-target="#deleteModal<?php echo $product['id']; ?>">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Edit & Delete Modals for Each Product -->
<?php
// Reset products result pointer and fetch all products again for modals
mysqli_data_seek($products, 0);
while ($product = mysqli_fetch_assoc($products)):
?>
<!-- Edit Product Modal -->
<div class="modal fade" id="editProductModal<?php echo $product['id']; ?>" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form method="POST" enctype="multipart/form-data">
                <div class="modal-header">
                    <h5 class="modal-title">Edit Product</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="action" value="edit">
                    <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">
                    <div class="mb-3">
                        <label class="form-label">Product Name</label>
                        <input type="text" class="form-control" name="name" value="<?php echo htmlspecialchars($product['name']); ?>" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Description</label>
                        <textarea class="form-control" name="description" rows="3" required><?php echo htmlspecialchars($product['description']); ?></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Price</label>
                        <input type="number" class="form-control" name="price" step="0.01" value="<?php echo $product['price']; ?>" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Stock</label>
                        <input type="number" class="form-control" name="stock_quantity" value="<?php echo $product['stock']; ?>" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Status</label>
                        <select class="form-select" name="status">
                            <option value="active" <?php echo $product['status'] == 'active' ? 'selected' : ''; ?>>Active</option>
                            <option value="inactive" <?php echo $product['status'] == 'inactive' ? 'selected' : ''; ?>>Inactive</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-purple">Update Product</button>
                </div>
            </form>
        </div>
    </div>
</div>
<!-- Delete Product Modal -->
<div class="modal fade" id="deleteModal<?php echo $product['id']; ?>" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST">
                <div class="modal-header">
                    <h5 class="modal-title">Delete Product</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="action" value="delete">
                    <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">
                    <p>Are you sure you want to delete the product "<?php echo htmlspecialchars($product['name']); ?>"?</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger">Delete</button>
                </div>
            </form>
        </div>
    </div>
</div>
<?php endwhile; ?>

<!-- Add Product Modal -->
<div class="modal fade" id="addProductModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form method="POST" enctype="multipart/form-data">
                <div class="modal-header">
                    <h5 class="modal-title">Add New Product</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="action" value="add">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Product Name</label>
                                <input type="text" class="form-control" name="name" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Category</label>
                                <select class="form-select" name="category_id" required>
                                    <option value="">Select Category</option>
                                    <?php 
                                    mysqli_data_seek($categories, 0);
                                    while ($category = mysqli_fetch_assoc($categories)): 
                                    ?>
                                        <option value="<?php echo $category['id']; ?>">
                                            <?php echo htmlspecialchars($category['name']); ?>
                                        </option>
                                    <?php endwhile; ?>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Description</label>
                        <textarea class="form-control" name="description" rows="3"></textarea>
                    </div>
                    <div class="row">
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label class="form-label">Price</label>
                                <input type="number" class="form-control" name="price" step="0.01" required>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label class="form-label">Stock Quantity</label>
                                <input type="number" class="form-control" name="stock_quantity" required>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label class="form-label">Status</label>
                                <select class="form-select" name="status">
                                    <option value="active">Active</option>
                                    <option value="inactive">Inactive</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Product Image</label>
                        <input type="file" class="form-control" name="image" accept="image/*" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-purple">Add Product</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>

